import { motion } from "motion/react";

export default function PrivacyPolicy() {
  return (
    <div className="bg-white min-h-screen pt-32 pb-24">
      <div className="max-w-3xl mx-auto px-6 md:px-12">
        <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.8 }}>
          <div className="flex items-center gap-4 text-xs font-mono text-emerald uppercase tracking-widest mb-12">
            <div className="w-8 h-[1px] bg-emerald"></div>
            <span>LEGAL</span>
          </div>
          <h1 className="text-4xl md:text-5xl font-heading font-medium text-navy mb-4 tracking-tight">Privacy Policy</h1>
          <p className="text-sm text-slate-400 mb-12">Last updated: June 2026</p>

          <div className="space-y-8 text-slate leading-relaxed">
            <p>
              Gayatri Enterprises ("we", "us") respects your privacy. This page explains what information we collect
              through this website and how it's used.
            </p>

            <div>
              <h2 className="text-xl font-heading font-medium text-navy mb-3">What we collect</h2>
              <p>
                Our contact, bulk order, and online order forms ask for your name, company, email, and the details of
                your enquiry. When you submit a form, it opens a pre-filled email in your own email client — the
                information is sent directly to our team's inbox and is not stored in any database on this website.
              </p>
            </div>

            <div>
              <h2 className="text-xl font-heading font-medium text-navy mb-3">How we use it</h2>
              <p>
                We use the information you submit only to respond to your enquiry, prepare quotations, and process
                orders. We do not sell or share your information with third parties for marketing purposes.
              </p>
            </div>

            <div>
              <h2 className="text-xl font-heading font-medium text-navy mb-3">Cookies &amp; analytics</h2>
              <p>
                This site does not currently use tracking cookies or third-party analytics. If that changes, this
                policy will be updated.
              </p>
            </div>

            <div>
              <h2 className="text-xl font-heading font-medium text-navy mb-3">Contact us</h2>
              <p>
                Questions about this policy or your data can be sent to{" "}
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
