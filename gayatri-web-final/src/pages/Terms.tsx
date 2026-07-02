import { motion } from "motion/react";

export default function Terms() {
  return (
    <div className="bg-white min-h-screen pt-32 pb-24">
      <div className="max-w-3xl mx-auto px-6 md:px-12">
        <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.8 }}>
          <div className="flex items-center gap-4 text-xs font-mono text-emerald uppercase tracking-widest mb-12">
            <div className="w-8 h-[1px] bg-emerald"></div>
            <span>LEGAL</span>
          </div>
          <h1 className="text-4xl md:text-5xl font-heading font-medium text-navy mb-4 tracking-tight">Terms of Use</h1>
          <p className="text-sm text-slate-400 mb-12">Last updated: June 2026</p>

          <div className="space-y-8 text-slate leading-relaxed">
            <div>
              <h2 className="text-xl font-heading font-medium text-navy mb-3">Orders &amp; enquiries</h2>
              <p>
                Forms on this site (Contact, Bulk Order, Online Order) submit a request — they are not a live
                checkout and no payment is collected through this website. Pricing, availability, and final order
                confirmation are handled directly by our team over email or phone.
              </p>
            </div>

            <div>
              <h2 className="text-xl font-heading font-medium text-navy mb-3">Product information</h2>
              <p>
                Product descriptions, pack sizes, batch numbers, and assay values shown on this site are indicative.
                Always refer to the Certificate of Analysis (CoA) supplied with your actual order for definitive
                specifications.
              </p>
            </div>

            <div>
              <h2 className="text-xl font-heading font-medium text-navy mb-3">Use of this site</h2>
              <p>
                Content on this site — including text, images, and the Gayatri Enterprises name and logo — belongs
                to Gayatri Enterprises and may not be reproduced without permission.
              </p>
            </div>

            <div>
              <h2 className="text-xl font-heading font-medium text-navy mb-3">Contact</h2>
              <p>
                Questions about these terms can be sent to{" "}
                <a href="mailto:procurement@gayatrient.com" className="text-emerald font-medium hover:text-emerald-deep">
                  procurement@gayatrient.com
                </a>
                .
              </p>
            </div>
          </div>
        </motion.div>
      </div>
    </div>
  );
}
