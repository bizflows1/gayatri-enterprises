import { useState, type FormEvent } from "react";
import { motion } from "motion/react";
import { Link, useNavigate } from "react-router-dom";
import { useAuth } from "../contexts/AuthContext";
import { ApiError } from "../lib/api";

export default function Register() {
  const { register } = useAuth();
  const navigate = useNavigate();
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  async function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setError(null);
    setSubmitting(true);

    const form = new FormData(e.currentTarget);

    try {
      await register({
        name: String(form.get("name")),
        email: String(form.get("email")),
        password: String(form.get("password")),
        company_name: String(form.get("company_name")),
        gstin: String(form.get("gstin") ?? "") || undefined,
        phone: String(form.get("phone") ?? "") || undefined,
      });
      navigate("/online-order");
    } catch (err) {
      setError(err instanceof ApiError ? err.message : "Something went wrong. Please try again.");
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="bg-white min-h-screen pt-32 pb-24">
      <div className="max-w-md mx-auto px-6">
        <motion.div
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8 }}
          className="mb-12"
        >
          <div className="flex items-center gap-4 text-xs font-mono text-emerald uppercase tracking-widest mb-12">
            <div className="w-8 h-[1px] bg-emerald"></div>
            <span>CLIENT PORTAL</span>
          </div>
          <h1 className="text-4xl md:text-5xl font-heading font-medium text-navy mb-6 tracking-tight leading-[0.95]">
            Create your account.
          </h1>
          <p className="text-lg text-slate font-light">
            Register your institution to start ordering, tracking dispatch, and managing your account.
          </p>
        </motion.div>

        {error && (
          <div className="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-4 mb-6">{error}</div>
        )}

        <form onSubmit={handleSubmit} className="space-y-5">
          <div className="space-y-2">
            <label className="text-xs uppercase tracking-widest font-semibold text-slate-500">Your Name *</label>
            <input name="name" required type="text" className="w-full bg-white px-4 py-3 rounded-lg border border-border focus:border-emerald focus:ring-1 focus:ring-emerald outline-none" />
          </div>
          <div className="space-y-2">
            <label className="text-xs uppercase tracking-widest font-semibold text-slate-500">Company / Institution *</label>
            <input name="company_name" required type="text" className="w-full bg-white px-4 py-3 rounded-lg border border-border focus:border-emerald focus:ring-1 focus:ring-emerald outline-none" />
          </div>
          <div className="space-y-2">
            <label className="text-xs uppercase tracking-widest font-semibold text-slate-500">Email *</label>
            <input name="email" required type="email" className="w-full bg-white px-4 py-3 rounded-lg border border-border focus:border-emerald focus:ring-1 focus:ring-emerald outline-none" />
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <label className="text-xs uppercase tracking-widest font-semibold text-slate-500">Phone</label>
              <input name="phone" type="tel" className="w-full bg-white px-4 py-3 rounded-lg border border-border focus:border-emerald focus:ring-1 focus:ring-emerald outline-none" />
            </div>
            <div className="space-y-2">
              <label className="text-xs uppercase tracking-widest font-semibold text-slate-500">GSTIN</label>
              <input name="gstin" type="text" maxLength={15} className="w-full bg-white px-4 py-3 rounded-lg border border-border focus:border-emerald focus:ring-1 focus:ring-emerald outline-none" />
            </div>
          </div>
          <div className="space-y-2">
            <label className="text-xs uppercase tracking-widest font-semibold text-slate-500">Password *</label>
            <input name="password" required minLength={8} type="password" className="w-full bg-white px-4 py-3 rounded-lg border border-border focus:border-emerald focus:ring-1 focus:ring-emerald outline-none" />
            <p className="text-xs text-slate/60">At least 8 characters.</p>
          </div>

          <button
            type="submit"
            disabled={submitting}
            className="w-full bg-emerald text-white px-10 py-4 rounded-full font-medium tracking-wide uppercase text-sm hover:bg-emerald-deep transition-colors shadow-md disabled:opacity-60"
          >
            {submitting ? "Creating account…" : "Create Account"}
          </button>
        </form>

        <p className="text-sm text-slate mt-8 text-center">
          Already registered?{" "}
          <Link to="/login" className="text-emerald font-medium">
            Sign in
          </Link>
        </p>
      </div>
    </div>
  );
}
