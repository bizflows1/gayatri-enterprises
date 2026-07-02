import { useState } from "react";
import { Calendar, User, ArrowLeft, Share2, Check, FlaskConical } from "lucide-react";
import { Link, useParams } from "react-router-dom";
import { useAllPosts } from "../hooks/useAllPosts";
import NotFound from "./NotFound";

export default function BlogPost() {
  const { id } = useParams();
  const { posts, loading } = useAllPosts();
  const post = posts.find((p) => p.id === id);
  const [copied, setCopied] = useState(false);

  // Wait for the AI-posts fetch to settle before declaring 404 — an
  // AI-generated post's id only exists once /api/posts has responded.
  if (!post) return loading ? null : <NotFound />;

  async function handleShare() {
    const shareData = { title: post.title, url: window.location.href };
    if (navigator.share) {
      try {
        await navigator.share(shareData);
      } catch {
        // user cancelled the share sheet — no action needed
      }
      return;
    }
    await navigator.clipboard.writeText(shareData.url);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  }

  return (
    <div className="bg-white min-h-screen pt-32 pb-24">
      <div className="max-w-4xl mx-auto px-6 md:px-12">
        <Link to="/blog" className="inline-flex items-center text-emerald font-medium hover:text-navy mb-10 transition-colors">
          <ArrowLeft className="w-4 h-4 mr-2" /> Back to all articles
        </Link>

        <article>
          <header className="mb-12">
            <span className="text-emerald font-bold tracking-widest uppercase text-sm mb-4 block">{post.category}</span>
            <h1 className="text-4xl md:text-5xl font-heading font-medium text-navy mb-8 leading-tight tracking-tight">
              {post.title}
            </h1>

            <div className="flex flex-col sm:flex-row sm:items-center justify-between thin-border-t thin-border-b py-6">
              <div className="flex items-center space-x-6 text-sm text-slate-500 mb-4 sm:mb-0">
                <span className="flex items-center"><User className="w-5 h-5 mr-2" /> {post.author}</span>
                <span className="flex items-center"><Calendar className="w-5 h-5 mr-2" /> {post.date}</span>
              </div>
              <button onClick={handleShare} className="flex items-center text-navy font-medium hover:text-emerald transition-colors">
                {copied ? <Check className="w-4 h-4 mr-2" /> : <Share2 className="w-4 h-4 mr-2" />}
                {copied ? "Link Copied" : "Share Article"}
              </button>
            </div>
          </header>

          <div className="w-full h-80 md:h-[500px] bg-soft-bg rounded-2xl mb-12 relative overflow-hidden border border-border">
            {post.image ? (
              <img src={post.image} alt={post.title} className="absolute inset-0 w-full h-full object-cover" />
            ) : (
              // AI-generated posts (App\Console\Commands\GenerateDailyBlog)
              // don't set an image_url — a plain icon reads cleaner than a
              // broken <img> or an empty box.
              <div className="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-navy to-emerald-deep">
                <FlaskConical className="w-16 h-16 text-white/30" strokeWidth={1.5} />
              </div>
            )}
          </div>

          {Array.isArray(post.content) ? (
            <div className="space-y-6 text-slate text-lg leading-relaxed">
              {post.content.map((para, i) => (
                <p key={i} className={i === 0 ? "text-xl text-navy font-medium" : ""}>
                  {para}
                </p>
              ))}
            </div>
          ) : (
            // Backend-generated posts come back as real HTML (h2/ul/table —
            // see GenerateDailyBlog's prompt), not a flat paragraph array.
            <div
              className="text-slate text-lg leading-relaxed space-y-6
                [&>h2]:text-2xl [&>h2]:font-heading [&>h2]:font-medium [&>h2]:text-navy [&>h2]:mt-10 [&>h2]:mb-4
                [&>h3]:text-xl [&>h3]:font-heading [&>h3]:font-medium [&>h3]:text-navy [&>h3]:mt-8 [&>h3]:mb-3
                [&>ul]:list-disc [&>ul]:pl-6 [&>ul]:space-y-2
                [&>table]:w-full [&>table]:text-base [&_th]:text-left [&_th]:text-navy [&_td]:py-2 [&_th]:py-2 [&_td]:border-t [&_th]:border-b [&_td]:border-border [&_th]:border-border"
              dangerouslySetInnerHTML={{ __html: post.content }}
            />
          )}

          <div className="mt-16 pt-8 thin-border-t flex justify-between">
            <Link to="/blog" className="text-sm font-semibold text-navy hover:text-emerald">
              More Insights
            </Link>
            <Link to="/contact" className="text-sm font-semibold text-emerald hover:text-emerald-deep">
              Ask our technical team &rarr;
            </Link>
          </div>
        </article>
      </div>
    </div>
  );
}
