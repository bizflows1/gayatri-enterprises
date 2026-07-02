<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Blog;
use Illuminate\Support\Str;
use Exception;

class GenerateDailyBlog extends Command
{
    protected $signature = 'app:generate-daily-blog';
    protected $description = 'Generate a daily blog using Gemini AI API';

    public function handle()
    {
        $this->info('Starting AI blog generation...');
        $apiKey = config('services.gemini.key');
        if (!$apiKey) {
            $apiKey = env('GEMINI_API_KEY');
        }

        if (!$apiKey) {
            $this->error('GEMINI_API_KEY is missing from configuration');
            return Command::FAILURE;
        }

        $allTopics = [
            'ISO 9001:2015 compliance in chemical warehousing and dispatch',
            'How Certificates of Analysis (CoA) protect lab procurement decisions',
            'AR grade vs LR grade vs HPLC grade reagents — when each matters',
            'Cold-chain storage requirements for temperature-sensitive lab reagents',
            'GHS hazard labeling and safe handling of corrosive and flammable solvents',
            'Batch traceability and lot-tracking systems in chemical distribution',
            'Common causes of reagent contamination during transport and storage',
            'How Indian pharma manufacturers source analytical-grade chemicals',
            'MSDS vs SDS — what laboratories actually need from suppliers',
            'Shelf life and expiry management for laboratory chemicals',
            'Choosing the right pack size for lab chemical bulk procurement',
            'Quality control checkpoints in chemical reagent manufacturing',
        ];

        // Pick 4 unique topics randomly
        $selectedTopics = collect($allTopics)->random(4)->toArray();

        foreach ($selectedTopics as $topic) {
            $this->info("Generating blog for topic: $topic");

            $prompt = "Write an extremely comprehensive, professional, and SEO-optimized long-form blog post for Gayatri Enterprises, a B2B distributor of laboratory chemicals, reagents, and glassware in India (ISO 9001:2015 certified, est. 1998), about: '{$topic}'.\n" .
                      "Audience: lab procurement managers, QC heads, and industrial/pharma chemists in India. Tone: precise, factual, no marketing fluff.\n" .
                      "CRITICAL REQUIREMENT: The blog MUST be at minimum 1000 to 1500 words long. Do not write a short summary. Provide deep technical analysis, practical examples, and accurate references to real standards (ISO, GHS, etc.) where relevant.\n" .
                      "Format the blog content purely in modern HTML (use <h2>, <h3>, <p>, <ul>, <li>, <strong>, <table>, etc.) without base <html> or <body> tags.\n" .
                      "Provide a compelling 'title', a short 'excerpt' (max 150 chars), a 'category' (one of: Compliance, Technical Guide, Market Trends, Safety, Company News), and the HTML 'content'.\n" .
                      "Return ONLY a valid JSON object with exactly these 4 string keys: 'title', 'excerpt', 'category', 'content'.";

            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

            try {
                $response = Http::timeout(120)->post($url, [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'responseMimeType' => 'application/json',
                    ]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                        $jsonText = $data['candidates'][0]['content']['parts'][0]['text'];
                        $blogData = json_decode($jsonText, true);

                        if (json_last_error() === JSON_ERROR_NONE && isset($blogData['title'], $blogData['content'])) {
                            $blog = Blog::create([
                                'title' => $blogData['title'],
                                'slug' => Str::slug($blogData['title']) . '-' . uniqid(),
                                'category' => $blogData['category'] ?? 'Technical Guide',
                                'author' => 'Gayatri Insights AI',
                                'content' => $blogData['content'],
                                'excerpt' => $blogData['excerpt'] ?? Str::limit(strip_tags($blogData['content']), 150),
                                'published_at' => now(),
                            ]);
                            $this->info("Successfully generated and saved blog: {$blog->title}");
                        } else {
                            $this->error("Failed to parse JSON from AI response.");
                        }
                    } else {
                        $this->error("Unexpected API response structure.");
                    }
                } else {
                    $errorMsg = "API Request failed: " . $response->body();
                    $this->error($errorMsg);
                    Log::error("GenerateDailyBlog: " . $errorMsg);
                }
            } catch (Exception $e) {
                $errorMsg = "Exception occurred: " . $e->getMessage();
                $this->error($errorMsg);
                Log::error("GenerateDailyBlog: " . $errorMsg);
            }
            
            // Sleep to avoid rate limits
            sleep(3);
        }

        return Command::SUCCESS;
    }
}
