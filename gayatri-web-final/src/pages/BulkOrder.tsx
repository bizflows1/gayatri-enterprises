import { useState, type FormEvent } from "react";
import { motion } from "motion/react";
import { PackageOpen, ShieldCheck, Truck, CheckCircle2, AlertCircle } from "lucide-react";
import { api, ApiError } from "../lib/api";

export default function BulkOrder() {
  const [submitting, setSubmitting] = useState(false);
  const [sent, setSent] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);
    setSubmitting(true);
    const form = new FormData(e.currentTarget);
    try {
      await api.post("/api/inquiries", {
        source: "bulk_order",
        name: form.get("contact"),
        email: form.get("email"),
        company: form.get("company"),
        industry: form.get("industry"),
        contact_person: form.get("contact"),
        requirements: form.get("requirements"),
        needs_msds_coa: form.get("msds") === "on",
      });
      setSent(true);
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Something went wrong. Please try again or email us directly.");
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
            <span>B2B PROCUREMENT</span>
          </div>
          <h1 className="text-5xl md:text-7xl font-heading font-medium text-navy mb-8 tracking-tight leading-[0.9]">
            Request Bulk<br />Catalog Pricing.
          </h1>
          <p className="text-xl text-slate font-light max-w-2xl">
            Submit your annualised requirements for laboratory chemicals or institutional supplies to receive our tiered B2B pricing structure.
          </p>
        </motion.div>

        <div className="max-w-4xl">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
          {[
            { icon: PackageOpen, title: "Volume Discounts", desc: "Tiered pricing for annual contracts." },
            { icon: Truck, title: "Priority Dispatch", desc: "Dedicated logistics handling." },
            { icon: ShieldCheck, title: "Dedicated Account", desc: "1-on-1 procurement manager." },
          ].map(({ icon: Icon, title, desc }) => (
            <div key={title} className="bg-soft-bg p-6 rounded-2xl flex items-center border border-border">
              <Icon className="w-10 h-10 text-emerald mr-4 shrink-0" />
              <div>
                <h4 className="font-heading font-medium text-navy text-sm">{title}</h4>
                <p className="text-xs text-slate">{desc}</p>
              </div>
            </div>
          ))}
        </div>

        <div className="bg-soft-bg rounded-[2rem] border border-border overflow-hidden">
          <div className="bg-navy px-8 py-6">
            <h3 className="text-xl font-heading font-medium text-white">Bulk Requirements Form</h3>
            <p className="text-white/60 text-sm mt-1">Please provide as much detail as possible to speed up the quotation process.</p>
          </div>

          {sent ? (
            <div className="p-10">
              <div className="bg-emerald-light border border-emerald/20 rounded-2xl p-8 flex items-start gap-4">
                <CheckCircle2 className="w-6 h-6 text-emerald shrink-0 mt-0.5" />
                <div>
                  <p className="font-semibold text-navy mb-1">Request received.</p>
                  <p className="text-sm text-slate">Our procurement team will contact you within one business day with pricing and availability. You can also reach us directly at procurement@gayatrient.com.</p>
                </div>
              </div>
            </div>
          ) : (
            <form className="p-8 md:p-12 space-y-8" onSubmit={handleSubmit}>
              <div>
                <h4 className="text-lg font-heading font-medium text-navy thin-border-b pb-2 mb-6">Company Details</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <label className="text-xs uppercase tracking-widest font-semibold text-slate-500">Company Name *</label>
                    <input name="company" required type="text" className="w-full bg-white px-4 py-3 rounded-lg border border-border focus:border-emerald focus:ring-1 focus:ring-emerald outline-none" />
                  </div>
                  <div className="space-y-2">
                    <label className="text-xs uppercase tracking-widest font-semibold text-slate-500">Industry / Sector *</label>
                    <select name="industry" className="w-full bg-white px-4 py-3 rounded-lg border border-border focus:border-emerald focus:ring-1 focus:ring-emerald outline-none">
                      <option>Pharmaceuticals</option>
                      <option>Academic / University</option>
                      <option>Healthcare / Clinical</option>
                      <option>Manufacturing</option>
                      <option>Other</option>
                    </select>
                  </div>
                  <div className="space-y-2">
                    <label className="text-xs uppercase tracking-widest font-semibold text-slate-500">Contact Person *</label>
                    <input name="contact" required type="text" className="w-full bg-white px-4 py-3 rounded-lg border border-border focus:border-emerald focus:ring-1 focus:ring-emerald outline-none" />
                  </div>
                  <div className="space-y-2">
                    <label className="text-xs uppercase tracking-widest font-semibold text-slate-500">Official Email *</label>
                    <input name="email" required type="email" className="w-full bg-white px-4 py-3 rounded-lg border border-border focus:border-emerald focus:ring-1 focus:ring-emerald outline-none" />
                  </div>
                </div>
              </div>

              <div>
                <h4 className="text-lg font-heading font-medium text-navy thin-border-b pb-2 mb-6">Product Requirements</h4>
                <p className="text-sm text-slate/70 mb-4">List the products, required grades, and estimated monthly/annual quantities.</p>
                <textarea
                  name="requirements"
                  rows={6}
                  required
                  placeholder={"e.g.\n1. Methanol, HPLC Grade, 2.5L x 50 bottles/month\n2. Sodium Chloride, AR Grade, 5kg x 10 packs/month"}
                  className="w-full bg-white px-4 py-3 rounded-lg border border-border focus:border-emerald focus:ring-1 focus:ring-emerald outline-none resize-y"
                />
                <div className="mt-6 flex items-center">
                  <input type="checkbox" id="msds" name="msds" className="w-4 h-4 text-emerald border-border rounded focus:ring-emerald mr-3" />
                  <label htmlFor="msds" className="text-sm text-slate">Include Material Safety Data Sheets (MSDS) &amp; COA with the quote.</label>
                </div>
              </div>

              {error && (
                <div className="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-4 flex items-start gap-2">
                  <AlertCircle className="w-4 h-4 shrink-0 mt-0.5" />
                  <span>{error}</span>
                </div>
              )}

              <div className="pt-4 text-right">
                <button type="submit" disabled={submitting} className="bg-emerald text-white px-10 py-5 rounded-full font-medium tracking-wide uppercase text-sm hover:bg-emerald-deep transition-colors shadow-md w-full md:w-auto disabled:opacity-60">
                  {submitting ? "Submitting…" : "Submit Bulk Request"}
                </button>
              </div>
            </form>
          )}
        </div>
        </div>
      </div>
    </div>
  );
}
