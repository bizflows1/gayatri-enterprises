import { useEffect, useState } from "react";
import { Newspaper } from "lucide-react";

const API_URL = import.meta.env.VITE_API_URL ?? "http://127.0.0.1:8000";

interface NewsItem {
  title: string;
  url: string;
  source: string;
  publishedAt: string;
  description: string;
}

function timeAgo(iso: string): string {
  const diffMs = Date.now() - new Date(iso).getTime();
  const hours = Math.floor(diffMs / 36e5);
  if (hours < 1) return "Just now";
  if (hours < 24) return `${hours}h ago`;
  return `${Math.floor(hours / 24)}d ago`;
}

export default function LiveFeed() {
  const [items, setItems] = useState<NewsItem[]>([]);

  useEffect(() => {
    fetch(`${API_URL}/api/blog-news`)
      .then((res) => (res.ok ? res.json() : { items: [] }))
      .then((data: { items: NewsItem[] }) => setItems(data.items ?? []))
      .catch(() => {
        // Backend not reachable — sidebar just stays empty, no crash.
      });
  }, []);

  if (items.length === 0) return null;

  return (
    <aside className="border border-border rounded-2xl overflow-hidden h-fit sticky top-28">
      <div className="bg-navy px-6 py-5 flex items-center gap-3">
        <span className="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center text-white shrink-0">
          <Newspaper size={16} />
        </span>
        <div>
          <h3 className="text-white font-heading font-medium leading-tight">Live Feed</h3>
          <p className="text-white/50 text-xs">Chemical &amp; Pharma Industry News</p>
        </div>
      </div>
      <ul className="divide-y divide-border max-h-[600px] overflow-y-auto">
        {items.map((item) => (
          <li key={item.url} className="p-5">
            <a href={item.url} target="_blank" rel="noopener noreferrer" className="group block">
              <span className="text-[11px] font-mono text-slate-400 block mb-2">{timeAgo(item.publishedAt)}</span>
              <h4 className="text-sm font-semibold text-navy leading-snug mb-1 group-hover:text-emerald transition-colors">
                {item.title}
              </h4>
              <p className="text-xs text-slate-500 line-clamp-2">{item.source}</p>
            </a>
          </li>
        ))}
      </ul>
    </aside>
  );
}
