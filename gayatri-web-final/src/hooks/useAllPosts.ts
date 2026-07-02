import { useEffect, useState } from "react";
import { blogs, type BlogPostData } from "../data/blogs";

const API_URL = import.meta.env.VITE_API_URL ?? "http://127.0.0.1:8000";

// Merges the static, hand-written posts with whatever the Laravel backend's
// Gemini-powered cron (App\Console\Commands\GenerateDailyBlog) has produced,
// via GET /api/blog. Falls back to the static list alone if that endpoint
// isn't reachable — the blog page should never be empty just because the
// backend is down or not deployed yet.
export function useAllPosts(): { posts: BlogPostData[]; loading: boolean } {
  const [aiPosts, setAiPosts] = useState<BlogPostData[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    fetch(`${API_URL}/api/blog`)
      .then((res) => (res.ok ? res.json() : { posts: [] }))
      .then((data: { posts: BlogPostData[] }) => {
        if (!cancelled) setAiPosts(data.posts ?? []);
      })
      .catch(() => {
        // Backend not reachable — static posts still show.
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });
    return () => {
      cancelled = true;
    };
  }, []);

  return { posts: [...aiPosts, ...blogs], loading };
}
