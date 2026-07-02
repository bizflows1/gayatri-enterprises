import { useEffect, useState } from "react";
import { motion } from "motion/react";
import { Link, Navigate } from "react-router-dom";
import { FileText, Package } from "lucide-react";
import { useAuth } from "../contexts/AuthContext";
import { api } from "../lib/api";

interface OrderItem {
  id: number;
  qty: string;
  unit_price: string;
  product: { name: string; pack_size: string };
}

interface Order {
  id: number;
  status: string;
  total: string;
  payment_status: string;
  payment_mode: string | null;
  created_at: string;
  items: OrderItem[];
  invoice: { invoice_no: string } | null;
}

const STATUS_STYLES: Record<string, string> = {
  draft: "bg-slate-100 text-slate-600",
  confirmed: "bg-emerald-light text-emerald-deep",
  packed: "bg-blue-50 text-blue-700",
  dispatched: "bg-blue-50 text-blue-700",
  delivered: "bg-emerald-light text-emerald-deep",
  cancelled: "bg-red-50 text-red-600",
};

const PAYMENT_MODE_LABELS: Record<string, string> = {
  cash: "Cash",
  cheque: "Cheque",
  neft: "NEFT / Bank Transfer",
};

export default function Account() {
  const { user, client, loading: authLoading, logout } = useAuth();
  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) return;
    api
      .get<{ data: Order[] }>("/api/orders")
      .then((res) => setOrders(res.data))
      .finally(() => setLoading(false));
  }, [user]);

  if (!authLoading && !user) {
    return <Navigate to="/login" replace />;
  }

  return (
    <div className="bg-white min-h-screen pt-32 pb-24">
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
              <span>MY ACCOUNT</span>
            </div>
            <h1 className="text-5xl md:text-7xl font-heading font-medium text-navy mb-8 tracking-tight leading-[0.9]">
              Order history.
            </h1>
            {client && (
              <p className="text-xl text-slate font-light">
                {client.company_name} &middot; Outstanding balance &#8377;{client.outstanding_balance}
              </p>
            )}
          </div>

          <button
            onClick={() => logout()}
            className="mt-8 md:mt-0 text-sm font-semibold uppercase tracking-widest text-slate hover:text-navy transition-colors"
          >
            Sign Out
          </button>
        </motion.div>

        {loading ? (
          <p className="text-slate text-sm">Loading your orders…</p>
        ) : orders.length === 0 ? (
          <div className="text-center py-24 bg-soft-bg rounded-2xl border border-border">
            <Package className="w-10 h-10 text-slate-400 mx-auto mb-4" />
            <p className="text-slate mb-6">You haven&apos;t placed any orders yet.</p>
            <Link to="/online-order" className="bg-emerald text-white px-8 py-3 rounded-full font-medium text-sm uppercase tracking-widest hover:bg-emerald-deep transition-colors">
              Start an Order
            </Link>
          </div>
        ) : (
          <div className="space-y-4">
            {orders.map((order) => (
              <div key={order.id} className="border border-border rounded-2xl p-6 md:p-8">
                <div className="flex flex-wrap items-start justify-between gap-4 mb-6">
                  <div>
                    <p className="text-xs font-mono text-slate-400 uppercase tracking-widest mb-1">
                      Order #{order.id} &middot; {new Date(order.created_at).toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" })}
                    </p>
                    <div className="flex items-center gap-3">
                      <span className={`text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-full ${STATUS_STYLES[order.status] ?? "bg-slate-100 text-slate-600"}`}>
                        {order.status}
                      </span>
                      {order.invoice && (
                        <span className="flex items-center gap-1.5 text-xs text-slate font-mono">
                          <FileText className="w-3.5 h-3.5" /> {order.invoice.invoice_no}
                        </span>
                      )}
                      {order.payment_mode && (
                        <span className="text-xs text-slate-400 uppercase tracking-wide">
                          {PAYMENT_MODE_LABELS[order.payment_mode] ?? order.payment_mode}
                        </span>
                      )}
                    </div>
                  </div>
                  <p className="text-2xl font-heading font-medium text-navy">&#8377;{order.total}</p>
                </div>

                <ul className="divide-y divide-border">
                  {order.items.map((item) => (
                    <li key={item.id} className="flex items-center justify-between py-3 text-sm">
                      <div>
                        <p className="text-navy font-medium">{item.product.name}</p>
                        <p className="text-slate-400 text-xs">{item.product.pack_size}</p>
                      </div>
                      <p className="text-slate">{item.qty} &times; &#8377;{item.unit_price}</p>
                    </li>
                  ))}
                </ul>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
