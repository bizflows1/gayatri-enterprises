import { motion, AnimatePresence } from "motion/react";
import { Search, ArrowRight, X, FlaskConical } from "lucide-react";
import { useMemo, useState, useEffect } from "react";
import { openMailto } from "../lib/mailto";
import { api } from "../lib/api";

const STORAGE_URL = `${import.meta.env.VITE_API_URL ?? "http://127.0.0.1:8000"}/storage`;

const SORTS = ["Name (A-Z)", "Category", "Price (Low–High)"];

interface ApiProduct {
  id: number;
  name: string;
  cas_number: string | null;
  grade: string | null;
  pack_size: string;
  unit: string;
  sales_price: string | null;
  available_qty: number;
  description: string | null;
  brand: { id: number; name: string } | null;
  category: { id: number; name: string } | null;
  images: { id: number; path: string; sort: number }[];
}

function FilterDropdown({
  label,
  value,
  options,
  onChange,
}: {
  label: string;
  value: string;
  options: string[];
  onChange: (v: string) => void;
}) {
  return (
    <div>
      <label className="text-xs text-slate-400 mb-1.5 block">{label}</label>
      <select
        value={value}
        onChange={(e) => onChange(e.target.value)}
        className="w-full px-3 py-2.5 rounded-lg border border-border bg-soft-bg text-sm text-navy focus:outline-none focus:ring-2 focus:ring-emerald"
      >
        {options.map((o) => (
          <option key={o} value={o}>{o}</option>
        ))}
      </select>
    </div>
  );
}

function ProductDetailModal({ product, onClose }: { product: ApiProduct; onClose: () => void }) {
  const [sent, setSent] = useState(false);
  const imageUrl = product.images[0] ? `${STORAGE_URL}/${product.images[0].path}` : null;

  function handleRequest() {
    openMailto("orders@gayatrient.com", `Product Enquiry — ${product.name}`, [
      ["Product", `${product.name}${product.cas_number ? ` (CAS: ${product.cas_number})` : ""}`],
      ["Brand", product.brand?.name ?? "—"],
      ["Grade", product.grade ?? "—"],
      ["Pack Size", product.pack_size],
    ]);
    setSent(true);
  }

  return (
    <motion.div
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
      className="fixed inset-0 z-50 bg-navy/60 backdrop-blur-sm flex items-center justify-center p-4 md:p-8"
      onClick={onClose}
    >
      <motion.div
        initial={{ opacity: 0, y: 30, scale: 0.97 }}
        animate={{ opacity: 1, y: 0, scale: 1 }}
        exit={{ opacity: 0, y: 20, scale: 0.97 }}
        transition={{ duration: 0.3, ease: "easeOut" }}
        onClick={(e) => e.stopPropagation()}
        className="bg-white rounded-3xl overflow-hidden w-full max-w-4xl max-h-[90vh] overflow-y-auto grid md:grid-cols-2 relative"
      >
        <div className="h-64 md:h-full bg-soft-bg flex items-center justify-center">
          {imageUrl ? (
            <img src={imageUrl} alt={product.name} className="w-full h-full object-cover" />
          ) : (
            <FlaskConical className="w-16 h-16 text-slate-300" />
          )}
        </div>

        <div className="p-8 md:p-10">
          <button
            onClick={onClose}
            aria-label="Close"
            className="absolute top-6 right-6 w-9 h-9 rounded-full border border-border bg-white flex items-center justify-center text-navy hover:bg-soft-bg transition-colors"
          >
            <X size={18} />
          </button>

          {product.brand && (
            <span className="text-[11px] font-bold text-emerald uppercase tracking-widest">{product.brand.name}</span>
          )}
          <h2 className="text-3xl font-heading font-medium text-navy mt-2 mb-1 pr-10">{product.name}</h2>
          {(product.cas_number || product.grade) && (
            <p className="text-sm text-slate-400 font-mono mb-6">
              {[product.cas_number && `CAS ${product.cas_number}`, product.grade].filter(Boolean).join(" · ")}
            </p>
          )}

          <h4 className="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-3">Pack Details</h4>
          <div className="thin-border-t pt-3 pb-6 space-y-2 text-sm text-slate">
            <div className="flex justify-between">
              <span>Pack Size</span>
              <span className="text-navy font-medium">{product.pack_size}</span>
            </div>
            <div className="flex justify-between">
              <span>Availability</span>
              <span className={`font-medium ${product.available_qty > 0 ? "text-emerald" : "text-red-500"}`}>
                {product.available_qty > 0 ? "In Stock" : "Out of Stock"}
              </span>
            </div>
            {product.sales_price && (
              <div className="flex justify-between">
                <span>Unit Price</span>
                <span className="text-navy font-medium">&#8377;{product.sales_price}</span>
              </div>
            )}
          </div>

          {product.description && (
            <p className="text-sm text-slate leading-relaxed mb-6">{product.description}</p>
          )}

          {sent ? (
            <div className="bg-emerald-light border border-emerald/20 rounded-xl p-5 text-sm text-slate">
              Your enquiry has opened in your email client — send it and our team will revert with pricing &amp; availability.
            </div>
          ) : (
            <button
              onClick={handleRequest}
              className="w-full bg-emerald text-white py-3.5 rounded-full font-medium text-sm uppercase tracking-widest hover:bg-emerald-deep transition-colors"
            >
              Request Order
            </button>
          )}
        </div>
      </motion.div>
    </motion.div>
  );
}

export default function Products() {
  const [products, setProducts] = useState<ApiProduct[]>([]);
  const [loading, setLoading] = useState(true);
  const [fetchError, setFetchError] = useState<string | null>(null);
  const [activeCat, setActiveCat] = useState("All");
  const [activeBrand, setActiveBrand] = useState("All");
  const [sort, setSort] = useState(SORTS[0]);
  const [query, setQuery] = useState("");
  const [selected, setSelected] = useState<ApiProduct | null>(null);

  useEffect(() => {
    api
      .get<{ data: ApiProduct[] }>("/api/products?per_page=200")
      .then((res) => setProducts(res.data))
      .catch(() => setFetchError("Could not load the catalog. Please try refreshing."))
      .finally(() => setLoading(false));
  }, []);

  const categories = useMemo(() => {
    const seen = new Set<string>();
    products.forEach((p) => { if (p.category?.name) seen.add(p.category.name); });
    return ["All", ...Array.from(seen).sort()];
  }, [products]);

  const brands = useMemo(() => {
    const seen = new Set<string>();
    products.forEach((p) => { if (p.brand?.name) seen.add(p.brand.name); });
    return ["All", ...Array.from(seen).sort()];
  }, [products]);

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase();
    const list = products.filter((p) => {
      const inCat = activeCat === "All" || p.category?.name === activeCat;
      const inBrand = activeBrand === "All" || p.brand?.name === activeBrand;
      const matches =
        q === "" ||
        p.name.toLowerCase().includes(q) ||
        (p.cas_number?.toLowerCase().includes(q) ?? false);
      return inCat && inBrand && matches;
    });

    if (sort === "Name (A-Z)") return [...list].sort((a, b) => a.name.localeCompare(b.name));
    if (sort === "Category") return [...list].sort((a, b) => (a.category?.name ?? "").localeCompare(b.category?.name ?? ""));
    if (sort === "Price (Low–High)") return [...list].sort((a, b) => parseFloat(a.sales_price ?? "0") - parseFloat(b.sales_price ?? "0"));
    return list;
  }, [activeCat, activeBrand, sort, query, products]);

  return (
    <div className="bg-white min-h-screen pt-32 pb-32">
      <div className="max-w-[90rem] mx-auto px-6 md:px-12">
        <motion.div
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8 }}
          className="mb-16 md:flex justify-between items-end"
        >
          <div className="max-w-2xl">
            <div className="flex items-center gap-4 text-xs font-mono text-emerald uppercase tracking-widest mb-12">
              <div className="w-8 h-[1px] bg-emerald"></div>
              <span>OUR CATALOG</span>
            </div>
            <h1 className="text-5xl md:text-7xl font-heading font-medium text-navy mb-8 tracking-tight leading-[0.9]">
              Formulation<br />Index.
            </h1>
            <p className="text-xl text-slate font-light">Direct access to our certified inventory of analytical reagents and bulk chemicals.</p>
          </div>

          <div className="mt-12 md:mt-0 w-full md:w-[400px]">
            <div className="relative w-full thin-border-b pb-2">
              <Search className="absolute left-0 top-1/2 -translate-y-1/2 text-navy" size={20} />
              <input
                type="text"
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                placeholder="Search by name or CAS number..."
                className="w-full pl-10 pr-4 py-3 bg-transparent border-none focus:ring-0 outline-none text-navy placeholder:text-slate-400 text-lg font-light"
              />
            </div>
          </div>
        </motion.div>

        <div className="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-12">
          <aside className="space-y-10">
            <div>
              <h4 className="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-4">Sort</h4>
              <FilterDropdown label="" value={sort} options={SORTS} onChange={setSort} />
            </div>

            <div>
              <h4 className="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-4">Filter</h4>
              <div className="space-y-5">
                <FilterDropdown label="Category" value={activeCat} options={categories} onChange={setActiveCat} />
                <FilterDropdown label="Brand" value={activeBrand} options={brands} onChange={setActiveBrand} />
              </div>
            </div>
          </aside>

          <div>
            {fetchError ? (
              <p className="text-red-500 text-sm">{fetchError}</p>
            ) : loading ? (
              <p className="text-slate text-sm">Loading catalog…</p>
            ) : (
              <>
                <p className="text-sm text-slate mb-6">{filtered.length} formulation(s) found</p>

                <motion.div layout className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                  {filtered.map((prod, i) => (
                    <motion.button
                      layout
                      key={prod.id}
                      initial={{ opacity: 0, y: 10 }}
                      animate={{ opacity: 1, y: 0 }}
                      transition={{ duration: 0.4, delay: Math.min(i, 8) * 0.05 }}
                      onClick={() => setSelected(prod)}
                      className="text-left bg-white border border-border rounded-2xl overflow-hidden group hover:shadow-lg hover:border-emerald/30 transition-all duration-500"
                    >
                      <div className="h-44 overflow-hidden bg-soft-bg flex items-center justify-center">
                        {prod.images[0] ? (
                          <img
                            src={`${STORAGE_URL}/${prod.images[0].path}`}
                            alt={prod.name}
                            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                          />
                        ) : (
                          <FlaskConical className="w-10 h-10 text-slate-300" />
                        )}
                      </div>
                      <div className="p-6">
                        <span className="text-[11px] font-bold text-emerald uppercase tracking-widest">{prod.brand?.name ?? ""}</span>
                        <h3 className="font-heading font-medium text-navy text-lg mt-2 mb-1 group-hover:text-emerald transition-colors duration-500">{prod.name}</h3>
                        <p className="text-xs text-slate-400 font-mono mb-5">
                          {prod.cas_number ?? "—"}{prod.grade ? ` · ${prod.grade}` : ""}
                        </p>
                        <div className="flex items-center justify-between thin-border-t pt-4">
                          <span className="text-xs text-slate/60">Request Quote</span>
                          <span className="w-8 h-8 rounded-full border border-border flex items-center justify-center text-navy group-hover:bg-navy group-hover:text-white transition-all shrink-0">
                            <ArrowRight size={14} />
                          </span>
                        </div>
                      </div>
                    </motion.button>
                  ))}
                </motion.div>

                {filtered.length === 0 && !loading && (
                  <div className="text-center py-32 text-slate font-light text-xl">No formulations found matching this criteria.</div>
                )}
              </>
            )}
          </div>
        </div>
      </div>

      <AnimatePresence>
        {selected && <ProductDetailModal product={selected} onClose={() => setSelected(null)} />}
      </AnimatePresence>
    </div>
  );
}
