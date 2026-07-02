import { motion } from "motion/react";
import { Calendar, User, ArrowRight, Clock, FlaskConical } from "lucide-react";
import { Link } from "react-router-dom";
import { useAllPosts } from "../hooks/useAllPosts";
import LiveFeed from "../components/LiveFeed";

export default function Blog() {
  const { posts } = useAllPosts();
  const [featured, ...rest] = posts;
  const gridPosts = rest.slice(0, 5);
  const olderPosts = rest.slice(5);

  if (!featured) return null;

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
            <span>OUR INSIGHTS</span>
          </div>
          <h1 className="text-5xl md:text-7xl font-heading font-medium text-navy mb-8 tracking-tight leading-[0.9]">
            Industry Insights<br />&amp; News.
          </h1>
          <p className="text-xl text-slate font-light">
            Technical guides, compliance updates, and company announcements from our quality and procurement experts.
          </p>
        </motion.div>

        <div className="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-12">
          <div>
            {/* Featured Post */}
            <Link to={`/blog/${featured.id}`} className="block mb-16 bg-soft-bg rounded-2xl overflow-hidden border border-border group">
              <div className="grid grid-cols-1 lg:grid-cols-2">
                <div className="h-64 lg:h-auto relative overflow-hidden">
                  {featured.image ? (
                    <img src={featured.image} alt={featured.title} className="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                  ) : (
                    <div className="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-navy to-emerald-deep">
                      <FlaskConical className="w-12 h-12 text-white/30" strokeWidth={1.5} />
                    </div>
                  )}
                </div>
                <div className="p-8 md:p-12 flex flex-col justify-center">
                  <span className="text-emerald font-bold tracking-widest uppercase text-sm mb-4">{featured.category}</span>
                  <h2 className="text-2xl lg:text-3xl font-heading font-medium text-navy mb-6 leading-tight group-hover:text-emerald transition-colors">
                    {featured.title}
                  </h2>
                  <p className="text-slate text-lg mb-8 leading-relaxed">{featured.excerpt}</p>
                  <div className="flex items-center text-sm text-slate-500 mb-8 space-x-6">
                    <span className="flex items-center"><Calendar className="w-4 h-4 mr-2" /> {featured.date}</span>
                    <span className="flex items-center"><User className="w-4 h-4 mr-2" /> {featured.author}</span>
                  </div>
                  <span className="inline-flex items-center text-navy font-bold group-hover:text-emerald transition-colors">
                    Read Full Article <ArrowRight className="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" />
                  </span>
                </div>
              </div>
            </Link>

            {/* Post Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-10 mb-16">
              {gridPosts.map((post) => (
                <Link key={post.id} to={`/blog/${post.id}`} className="border border-border rounded-2xl overflow-hidden hover:shadow-lg transition-shadow group">
                  <div className="h-48 relative overflow-hidden">
                    {post.image ? (
                      <img src={post.image} alt={post.title} className="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" loading="lazy" />
                    ) : (
                      <div className="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-navy to-emerald-deep">
                        <FlaskConical className="w-10 h-10 text-white/30" strokeWidth={1.5} />
                      </div>
                    )}
                  </div>
                  <div className="p-8">
                    <span className="text-emerald text-xs font-bold uppercase tracking-wider mb-3 block">{post.category}</span>
                    <h3 className="text-2xl font-heading font-medium text-navy mb-4 group-hover:text-emerald transition-colors">
                      {post.title}
                    </h3>
                    <p className="text-slate mb-6 line-clamp-2">{post.excerpt}</p>
                    <div className="flex items-center justify-between text-sm text-slate-500 border-t border-border pt-6">
                      <span className="flex items-center"><Calendar className="w-4 h-4 mr-1.5" /> {post.date}</span>
                      <span className="text-navy font-medium group-hover:text-emerald">Read More</span>
                    </div>
                  </div>
                </Link>
              ))}
            </div>

            {/* Older Articles */}
            {olderPosts.length > 0 && (
              <div>
                <div className="flex items-center gap-3 mb-6 thin-border-b pb-4">
                  <Clock className="w-4 h-4 text-slate-400" />
                  <h3 className="font-heading font-medium text-navy text-lg">Older Articles</h3>
                </div>
                <ul className="divide-y divide-border">
                  {olderPosts.map((post) => (
                    <li key={post.id}>
                      <Link to={`/blog/${post.id}`} className="flex items-baseline justify-between gap-6 py-5 group">
                        <div>
                          <h4 className="font-heading font-medium text-navy group-hover:text-emerald transition-colors">{post.title}</h4>
                          <p className="text-slate text-sm mt-1 line-clamp-1">{post.excerpt}</p>
                        </div>
                        <span className="text-xs font-mono text-slate-400 shrink-0">{post.date}</span>
                      </Link>
                    </li>
                  ))}
                </ul>
              </div>
            )}
          </div>

          <LiveFeed />
        </div>
      </div>
    </div>
  );
}
