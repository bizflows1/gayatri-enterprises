<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * JSON API for the public Insights page — the React frontend calls this
     * directly (api/blog), no Blade views involved. Scoped to the actual
     * industry (lab chemicals/pharma), not finance.
     */
    public function index()
    {
        $posts = Blog::latest('published_at')->take(30)->get()->map(function (Blog $blog) {
            return [
                'id' => $blog->slug,
                'title' => $blog->title,
                'excerpt' => $blog->excerpt,
                'category' => $blog->category,
                'date' => optional($blog->published_at)->format('d M Y'),
                'author' => $blog->author,
                'image' => $blog->image_url,
                'content' => $blog->content,
            ];
        });

        return response()->json(['posts' => $posts]);
    }

    public function show(string $slug)
    {
        $blog = Blog::where('slug', $slug)->first();

        if (!$blog) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json([
            'id' => $blog->slug,
            'title' => $blog->title,
            'excerpt' => $blog->excerpt,
            'category' => $blog->category,
            'date' => optional($blog->published_at)->format('d M Y'),
            'author' => $blog->author,
            'image' => $blog->image_url,
            'content' => $blog->content,
        ]);
    }

    /**
     * Free, no-API-key industry news — same Google News RSS approach proven
     * on the reference site, rescoped from finance to chemicals/pharma/lab
     * distribution. Cached 30 minutes so it isn't fetched on every request.
     */
    public function news()
    {
        $items = Cache::remember('gayatri_industry_news_v1', 1800, function () {
            try {
                $query = '("chemical industry" OR "pharmaceutical manufacturing" OR "laboratory reagents" OR "ISO 9001") India when:3d';
                $rssUrl = 'https://news.google.com/rss/search?q=' . urlencode($query) . '&hl=en-IN&gl=IN&ceid=IN:en';

                $response = Http::timeout(6)->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                ])->get($rssUrl);

                $news = [];
                if ($response->successful()) {
                    $xml = @simplexml_load_string($response->body());
                    if ($xml && isset($xml->channel->item)) {
                        foreach ($xml->channel->item as $item) {
                            $news[] = [
                                'title' => (string) $item->title,
                                'url' => (string) $item->link,
                                'source' => isset($item->source) ? (string) $item->source : 'Google News',
                                'description' => Str::limit(strip_tags((string) $item->description), 140),
                                'publishedAt' => date('c', strtotime((string) $item->pubDate)),
                                'timestamp' => strtotime((string) $item->pubDate),
                            ];
                        }
                        usort($news, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
                        $news = array_slice($news, 0, 15);
                        foreach ($news as &$n) {
                            unset($n['timestamp']);
                        }
                    }
                }
                return $news;
            } catch (\Exception $e) {
                \Log::warning('Industry news RSS fetch failed: ' . $e->getMessage());
                return [];
            }
        });

        return response()->json(['items' => $items]);
    }
}
