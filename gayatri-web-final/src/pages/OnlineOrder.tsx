import { useEffect, useMemo, useState } from "react";
import { motion } from "motion/react";
import { Lock, Plus, Trash2, CheckCircle2, UserPlus, AlertCircle } from "lucide-react";
import { Link } from "react-router-dom";
import { useAuth } from "../contexts/AuthContext";
import { api, ApiError } from "../lib/api";

interface ApiProduct {
  id: number;
  name: string;
  cas_number: string | null;
  pack_size: string;
  unit: string;
  sales_price: string | null;
  available_qty: number;
  brand?: { name: string } | null;
}

interface CartLine {
  productId: number;
  qty: number;
}

interface ConfirmedOrder {
  status: string;
  invoice: { invoice_no: string } | null;
  total: string;
}

export default function OnlineOrder() {
  const { user, client, loading: authLoading } = useAuth();
  const [products, setProducts] = useState<ApiProduct[]>([]);
  const [productsLoading, setProductsLoading] = useState(true);
  const [cart, setCart] = useState<CartLine[]>([]);
  const [paymentMode, setPaymentMode] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [confirmed, setConfirmed] = useState<ConfirmedOrder | null>(null);

  useEffect(() => {
    api
      .get<{ data: ApiProduct[] }>("/api/products?per_page=50")
      .then((res) => setProducts(res.data))
      .catch(() => setError("Could not load the catalog right now. Please refresh."))
      .finally(() => setProductsLoading(false));
  }, []);

  function addToCart(productId: number) {
    setCart((prev) => {
      const existing = prev.find((l) => l.productId === productId);
      if (existing) return prev.map((l) => (l === existing ? { ...l, qty: l.qty + 1 } : l));
      return [...prev, { productId, qty: 1 }];
    });
  }

  function updateQty(index: number, qty: number) {
    setCart((prev) => prev.map((l, i) => (i === index ? { ...l, qty: Math.max(1, qty) } : l)));
  }

  function removeLine(index: number) {
    setCart((prev) => prev.filter((_, i) => i !== index));
  }

  const totalItems = useMemo(() => cart.reduce((sum, l) => sum + l.qty, 0), [cart]);

  async function placeOrder() {
    if (!paymentMode) {
      setError("Please select how you'll be paying for this order.");
      return;
    }

    setError(null);
    setSubmitting(true);

    try {
      const order = await api.post<{ id: number }>("/api/orders", {
        items: cart.map((l) => ({ product_id: l.productId, qty: l.qty })),
        payment_mode: paymentMode,
      });
      const result = await api.post<ConfirmedOrder>(`/api/orders/${order.id}/confirm`);
      setConfirmed(result);
      setCart([]);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Something went wrong placing your order.");
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="bg-white min-h-screen pt-32 pb-24">
      <div className="max-w-[90rem] mx-auto px-6 md:px-12">
        <motion.div
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8 }}
          className="mb-16 max-w-3xl"
        >
          <div className="flex items-center gap-4 text-xs font-mono text-emerald uppercase tracking-widest mb-12">
            <div className="w-8 h-[1px] bg-emerald"></div>
            <span>ONLINE ORDER</span>
          </div>
          <h1 className="text-5xl md:text-7xl font-heading font-medium text-navy mb-8 tracking-tight leading-[0.9]">
            Build your order.
          </h1>
          <p className="text-xl text-slate font-light">
            Add items below and confirm — stock is reserved and your invoice generated instantly through the client portal.
          </p>
        </motion.div>

        {!authLoading && !user && (
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-16">
            <div className="bg-soft-bg border border-border rounded-2xl p-8 flex items-start gap-4">
              <span className="w-11 h-11 rounded-full bg-emerald-light flex items-center justify-center text-emerald shrink-0">
                <UserPlus className="w-5 h-5" />
              </span>
              <div>
                <h3 className="font-heading font-medium text-navy mb-1">New here?</h3>
                <p className="text-sm text-slate mb-3">Register your institution to place orders, track dispatch, and view invoices.</p>
                <Link to="/register" className="text-sm font-semibold text-emerald hover:text-navy transition-colors">Create an account &rarr;</Link>
              </div>
            </div>
            <div className="bg-soft-bg border border-border rounded-2xl p-8 flex items-start gap-4">
              <span className="w-11 h-11 rounded-full bg-navy/5 flex items-center justify-center text-navy/50 shrink-0">
                <Lock className="w-5 h-5" />
              </span>
              <div>
                <h3 className="font-heading font-medium text-navy mb-1">Existing client?</h3>
                <p className="text-sm text-slate mb-3">Sign in to access your account ledger and order history.</p>
                <Link to="/login" className="text-sm font-semibold text-emerald hover:text-navy transition-colors">Sign in &rarr;</Link>
              </div>
            </div>
          </div>
        )}

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-10">
          <div className="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-5">
            {productsLoading ? (
              <p className="text-slate text-sm col-span-2">Loading catalog…</p>
            ) : (
              products.map((p) => <ProductPickerCard key={p.id} product={p} onAdd={addToCart} />)
            )}
          </div>

          <div className="lg:col-span-1">
            <div className="sticky top-28 bg-soft-bg border border-border rounded-2xl p-7">
              <h3 className="font-heading font-medium text-navy text-lg mb-1">Your Order</h3>
              <p className="text-xs text-slate/60 mb-6">{totalItems} item(s) added</p>

              {confirmed ? (
                <div className="bg-emerald-light border border-emerald/20 rounded-xl p-6 flex items-start gap-3">
                  <CheckCircle2 className="w-5 h-5 text-emerald shrink-0 mt-0.5" />
                  <div>
                    <p className="text-sm text-slate font-medium mb-1">Order confirmed.</p>
                    <p className="text-sm text-slate">
                      Invoice <strong>{confirmed.invoice?.invoice_no}</strong> for &#8377;{confirmed.total} has been generated. Stock has been reserved for dispatch.
                    </p>
                  </div>
                </div>
              ) : cart.length === 0 ? (
                <p className="text-sm text-slate/60 py-8 text-center">No items yet — add a product from the catalog.</p>
              ) : (
                <>
                  <ul className="space-y-4 mb-6 max-h-80 overflow-y-auto pr-1">
                    {cart.map((line, i) => {
                      const product = products.find((p) => p.id === line.productId);
                      if (!product) return null;
                      return (
                        <li key={line.productId} className="flex items-start justify-between gap-3 text-sm">
                          <div>
                            <p className="font-semibold text-navy">{product.name}</p>
                            <p className="text-slate/60 text-xs">{product.pack_size}</p>
                          </div>
                          <div className="flex items-center gap-2 shrink-0">
                            <input
                              type="number"
                              min={1}
                              value={line.qty}
                              onChange={(e) => updateQty(i, Number(e.target.value))}
                              className="w-14 px-2 py-1 rounded border border-border text-center bg-white"
                            />
                            <button onClick={() => removeLine(i)} aria-label="Remove item" className="text-slate/40 hover:text-red-500 transition-colors">
                              <Trash2 className="w-4 h-4" />
                            </button>
                          </div>
                        </li>
                      );
                    })}
                  </ul>

                  {user && (
                    <div className="mb-4 pt-5 thin-border-t">
                      <label className="text-xs font-semibold uppercase tracking-widest text-slate/60 mb-2 block">
                        Payment Mode
                      </label>
                      <select
                        value={paymentMode}
                        onChange={(e) => setPaymentMode(e.target.value)}
                        className="w-full px-3 py-2.5 rounded-lg border border-border bg-white text-sm text-navy"
                      >
                        <option value="">Select how you'll pay…</option>
                        <option value="cash">Cash</option>
                        <option value="cheque">Cheque</option>
                        <option value="neft">NEFT / Bank Transfer</option>
                      </select>
                    </div>
                  )}

                  {error && (
                    <div className="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3 mb-4 flex items-start gap-2">
                      <AlertCircle className="w-4 h-4 shrink-0 mt-0.5" />
                      <span>{error}</span>
                    </div>
                  )}

                  {user ? (
                    <button
                      onClick={placeOrder}
                      disabled={submitting}
                      className="w-full bg-emerald text-white py-3 rounded-full font-medium uppercase tracking-wide text-sm hover:bg-emerald-deep transition-colors disabled:opacity-60"
                    >
                      {submitting ? "Placing order…" : "Confirm & Place Order"}
                    </button>
                  ) : (
                    <div className="pt-5 thin-border-t text-center">
                      <p className="text-sm text-slate mb-3">Sign in to place this order.</p>
                      <Link to="/login" className="inline-block w-full bg-navy text-white py-3 rounded-full font-medium uppercase tracking-wide text-sm hover:bg-navy/90 transition-colors">
                        Sign In to Continue
                      </Link>
                    </div>
                  )}
                </>
              )}

              {client && (
                <p className="text-[11px] text-slate/50 mt-5 leading-relaxed">
                  Ordering as <strong>{client.company_name}</strong>. Need help?{" "}
                  <Link to="/contact" className="text-emerald font-medium">Contact us</Link>.
                </p>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function ProductPickerCard({ product, onAdd }: { product: ApiProduct; onAdd: (productId: number) => void }) {
  const outOfStock = product.available_qty <= 0;

  return (
    <div className="bg-white border border-border rounded-2xl overflow-hidden flex flex-col group">
      <div className="p-6 flex flex-col flex-1">
        {product.brand && (
          <span className="text-[11px] font-bold text-emerald uppercase tracking-widest mb-2">{product.brand.name}</span>
        )}
        <h4 className="font-heading font-medium text-navy mb-1">{product.name}</h4>
        <p className="text-slate/50 font-mono text-xs mb-1">CAS: {product.cas_number ?? "—"}</p>
        <p className="text-slate text-sm mb-4">{product.pack_size} &middot; &#8377;{product.sales_price ?? "—"}</p>

        <div className="mt-auto">
          <button
            onClick={() => onAdd(product.id)}
            disabled={outOfStock}
            className="w-full py-2.5 rounded-lg border border-navy text-navy text-sm font-semibold uppercase tracking-wide flex items-center justify-center gap-2 hover:bg-navy hover:text-white transition-colors disabled:opacity-40 disabled:hover:bg-white disabled:hover:text-navy"
          >
            <Plus className="w-4 h-4" /> {outOfStock ? "Out of Stock" : "Add to Order"}
          </button>
        </div>
      </div>
    </div>
  );
}
