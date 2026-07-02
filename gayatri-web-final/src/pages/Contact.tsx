import { motion } from "motion/react";
import { Mail, Phone, MapPin, CheckCircle2, AlertCircle } from "lucide-react";
import { useState, type FormEvent } from "react";
import { api, ApiError } from "../lib/api";

export default function Contact() {
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
        source: "contact",
        name: form.get("name"),
        email: form.get("email"),
        institution: form.get("institution") || null,
        type: form.get("type"),
        message: form.get("message"),
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
           className="mb-24"
        >
          <div className="flex items-center gap-4 text-xs font-mono text-emerald uppercase tracking-widest mb-12">
             <div className="w-8 h-[1px] bg-emerald"></div>
             <span>GLOBAL REACH</span>
          </div>
          <h1 className="text-5xl md:text-7xl lg:text-[6rem] font-heading font-medium text-navy mb-8 tracking-tight leading-[0.9]">
            Let's connect.
          </h1>
          <p className="text-xl md:text-2xl text-slate font-light max-w-2xl">
            For institutional bulk enquiries, technical documentation audits, or specialized procurement, our team is ready to assist.
          </p>
        </motion.div>

        <div className="grid lg:grid-cols-[1fr_1.5fr] gap-16 md:gap-32 items-start pt-16 thin-border-t">
          <motion.div
             initial={{ opacity: 0, x: -20 }}
             animate={{ opacity: 1, x: 0 }}
             transition={{ delay: 0.2 }}
             className="space-y-16"
          >
             <div className="flex gap-6 items-start group">
                <div className="w-12 h-12 rounded-full border border-border flex items-center justify-center text-navy group-hover:bg-navy group-hover:text-white transition-all flex-shrink-0">
                   <MapPin size={20} />
                </div>
                <div>
                   <h3 className="text-xs uppercase tracking-widest font-semibold text-slate-500 mb-2">Headquarters</h3>
                   <p className="text-navy text-xl font-medium leading-relaxed">
                     Ground Floor, Pl19, Delhi Road,<br/>
                     Preet Vihar, City Park,<br/>
                     Hapur &ndash; 245101, Uttar Pradesh, India
                   </p>
                </div>
             </div>

             <div className="flex gap-6 items-start group">
                <div className="w-12 h-12 rounded-full border border-border flex items-center justify-center text-navy group-hover:bg-navy group-hover:text-white transition-all flex-shrink-0">
                   <Phone size={20} />
                </div>
                <div>
                   <h3 className="text-xs uppercase tracking-widest font-semibold text-slate-500 mb-2">Sales &middot; Manish Sharma</h3>
                   <p className="text-navy text-xl font-medium leading-relaxed">
                     +91 90677 80801<br/>
                     <span className="text-sm text-slate">+91 95576 43005 &middot; +91 75059 01237</span>
                   </p>
                </div>
             </div>

             <div className="flex gap-6 items-start group">
                <div className="w-12 h-12 rounded-full border border-border flex items-center justify-center text-navy group-hover:bg-navy group-hover:text-white transition-all flex-shrink-0">
                   <Mail size={20} />
                </div>
                <div>
                   <h3 className="text-xs uppercase tracking-widest font-semibold text-slate-500 mb-2">Direct Enquiries</h3>
                   <p className="text-navy text-xl font-medium leading-relaxed">
                     procurement@gayatrient.com<br/>
                     orders@gayatrient.com
                   </p>
                </div>
             </div>

             <div className="thin-border-t pt-8 text-xs font-mono text-slate-400 tracking-wide">
                GSTIN 09CPZPG0907C1ZT &middot; IEC CPZPG0907C
             </div>
          </motion.div>

          <motion.div
             initial={{ opacity: 0, y: 20 }}
             animate={{ opacity: 1, y: 0 }}
             transition={{ delay: 0.3 }}
             className="bg-soft-bg p-8 md:p-16 rounded-[2rem] border border-border"
          >
             <h2 className="text-3xl font-heading font-medium text-navy mb-12">Submit an Enquiry</h2>
             {sent ? (
               <div className="bg-emerald-light border border-emerald/20 rounded-2xl p-8 flex items-start gap-4">
                 <CheckCircle2 className="w-6 h-6 text-emerald shrink-0 mt-0.5" />
                 <div>
                   <p className="font-semibold text-navy mb-1">Enquiry received.</p>
                   <p className="text-sm text-slate">Our team will respond within one business day. You can also reach us directly at procurement@gayatrient.com.</p>
                 </div>
               </div>
             ) : (
             <form className="space-y-8" onSubmit={handleSubmit}>
                <div className="grid md:grid-cols-2 gap-8">
                   <div className="space-y-4">
                      <label className="text-xs uppercase tracking-widest font-semibold text-slate-500">Full Name</label>
                      <input name="name" required type="text" className="w-full bg-transparent border-b border-border py-2 focus:outline-none focus:border-navy transition-all text-navy text-lg" placeholder="Jane Doe" />
                   </div>
                   <div className="space-y-4">
                      <label className="text-xs uppercase tracking-widest font-semibold text-slate-500">Institution</label>
                      <input name="institution" type="text" className="w-full bg-transparent border-b border-border py-2 focus:outline-none focus:border-navy transition-all text-navy text-lg" placeholder="Acme Laboratories" />
                   </div>
                </div>
                <div className="grid md:grid-cols-2 gap-8">
                   <div className="space-y-4">
                      <label className="text-xs uppercase tracking-widest font-semibold text-slate-500">Email Address</label>
                      <input name="email" required type="email" className="w-full bg-transparent border-b border-border py-2 focus:outline-none focus:border-navy transition-all text-navy text-lg" placeholder="jane@example.com" />
                   </div>
                   <div className="space-y-4">
                      <label className="text-xs uppercase tracking-widest font-semibold text-slate-500">Requirement Type</label>
                      <select name="type" className="w-full bg-transparent border-b border-border py-2 focus:outline-none focus:border-navy transition-all text-navy text-lg appearance-none">
                         <option>Bulk Chemicals</option>
                         <option>Traceability / Documentation</option>
                         <option>Glassware &amp; Equipment</option>
                         <option>Other</option>
                      </select>
                   </div>
                </div>
                <div className="space-y-4">
                   <label className="text-xs uppercase tracking-widest font-semibold text-slate-500">Message</label>
                   <textarea name="message" required rows={4} className="w-full bg-transparent border-b border-border py-2 focus:outline-none focus:border-navy transition-all resize-none text-navy text-lg" placeholder="Include CAS numbers or specific assay requirements..."></textarea>
                </div>
                {error && (
                  <div className="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-4 flex items-start gap-2">
                    <AlertCircle className="w-4 h-4 shrink-0 mt-0.5" />
                    <span>{error}</span>
                  </div>
                )}
                <button type="submit" disabled={submitting} className="bg-navy text-white px-10 py-5 rounded-full font-medium tracking-wide uppercase text-sm hover:bg-emerald w-full md:w-auto transition-colors duration-300 disabled:opacity-60">
                   {submitting ? "Sending…" : "Transmit Request"}
                </button>
             </form>
             )}
          </motion.div>
        </div>
      </div>
    </div>
  );
}
