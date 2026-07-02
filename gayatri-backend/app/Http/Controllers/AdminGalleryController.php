<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminGalleryController extends Controller
{
    /**
     * Helper to enforce permissions.
     * Admins are always authorized, Staff must have 'manage_gallery' permission.
     */
    private function authorizeGallery()
    {
        abort_unless(auth()->user()->hasPermission('manage_gallery'), 403);
    }

    /**
     * Determine the safe folder path for gallery storage (Hostinger safe setup).
     */
    protected function getGalleryDir()
    {
        $dirPublic = public_path('assets/gallery');
        $hostingerDir = base_path('../assets/gallery');
        
        $dir = File::exists($hostingerDir) ? $hostingerDir : $dirPublic;
        
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        
        return $dir;
    }

    /**
     * Show the Gallery Management Workspace.
     */
    public function index()
    {
        $this->authorizeGallery();

        $dirToScan = $this->getGalleryDir();
        $images = [];

        if ($dirToScan && File::isDirectory($dirToScan)) {
            $files = File::files($dirToScan);
            foreach ($files as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'])) {
                    $filename = $file->getFilename();
                    $images[] = [
                        'filename' => $filename,
                        'url' => asset('assets/gallery/' . $filename),
                        'size' => round($file->getSize() / 1024, 1), // in KB
                        'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                    ];
                }
            }
            
            // Read custom gallery order from filesystem JSON
            $orderFile = $dirToScan . '/gallery_order.json';
            if (File::exists($orderFile)) {
                try {
                    $order = json_decode(File::get($orderFile), true);
                    if (is_array($order)) {
                        // Create a map of filename => position index
                        $orderMap = array_flip($order);
                        
                        usort($images, function ($a, $b) use ($orderMap) {
                            $posA = isset($orderMap[$a['filename']]) ? $orderMap[$a['filename']] : 999999;
                            $posB = isset($orderMap[$b['filename']]) ? $orderMap[$b['filename']] : 999999;
                            
                            if ($posA === $posB) {
                                return strcmp($b['modified'], $a['modified']); // Fallback to newest first
                            }
                            return $posA <=> $posB;
                        });
                    } else {
                        // Default to recently added
                        usort($images, function ($a, $b) {
                            return strcmp($b['modified'], $a['modified']);
                        });
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to parse gallery order JSON: ' . $e->getMessage());
                }
            } else {
                // Default to recently added
                usort($images, function ($a, $b) {
                    return strcmp($b['modified'], $a['modified']);
                });
            }
        }

        return view('admin.gallery.index', compact('images'));
    }

    /**
     * Save custom drag-and-drop sort order.
     */
    public function reorder(Request $request)
    {
        $this->authorizeGallery();

        $request->validate([
            'order' => 'required|array',
            'order.*' => 'string',
        ]);

        try {
            $dir = $this->getGalleryDir();
            $orderFile = $dir . '/gallery_order.json';
            
            // Clean filenames to prevent directory traversal
            $cleanOrder = array_map(function($filename) {
                return basename($filename);
            }, $request->input('order'));

            File::put($orderFile, json_encode($cleanOrder));

            return response()->json([
                'success' => true,
                'message' => 'Gallery order updated successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Gallery Reorder Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to save gallery order.'
            ], 500);
        }
    }

    /**
     * Save an optimized, cropped, and filtered WebP image.
     */
    public function store(Request $request)
    {
        $this->authorizeGallery();

        $request->validate([
            'image' => 'required|string', // base64 string
        ]);

        try {
            $base64Image = $request->input('image');
            
            // Extract metadata and raw data
            if (!preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                return response()->json(['error' => 'Invalid image data format.'], 400);
            }

            $rawImage = substr($base64Image, strpos($base64Image, ',') + 1);
            $rawImage = base64_decode($rawImage);

            if ($rawImage === false) {
                return response()->json(['error' => 'Base64 decode failed.'], 400);
            }

            // Generate unique, clean WebP filename
            $filename = 'gallery_' . Str::random(10) . '_' . time() . '.webp';
            $savePath = $this->getGalleryDir() . '/' . $filename;

            // Save the raw file
            File::put($savePath, $rawImage);

            // Create activity log entry for accountability
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Gallery Image Uploaded',
                'description' => 'Uploaded optimized image: ' . $filename . ' (' . round(strlen($rawImage) / 1024, 1) . ' KB)',
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'url' => asset('assets/gallery/' . $filename),
                'message' => 'Image compressed & uploaded successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Gallery Image Upload Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save image. Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete an image from the gallery.
     */
    public function destroy(Request $request)
    {
        $this->authorizeGallery();

        $request->validate([
            'filename' => 'required|string',
        ]);

        $filename = basename($request->input('filename')); // Ensure path traversal prevention
        $filePath = $this->getGalleryDir() . '/' . $filename;

        if (File::exists($filePath)) {
            File::delete($filePath);

            // Log the action
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Gallery Image Deleted',
                'description' => 'Deleted gallery image: ' . $filename,
                'ip_address' => $request->ip()
            ]);

            return response()->json(['success' => true, 'message' => 'Image deleted successfully!']);
        }

        return response()->json(['error' => 'Image file not found.'], 404);
    }

    /**
     * AI-powered image filter translation.
     * Takes natural language prompt and returns Canvas/CSS adjustment parameters.
     */
    public function aiAdjust(Request $request)
    {
        $this->authorizeGallery();

        $request->validate([
            'prompt' => 'required|string|max:500',
        ]);

        $prompt = $request->input('prompt');
        $apiKey = config('services.gemini.key');
        if (!$apiKey) {
            $apiKey = env('GEMINI_API_KEY');
        }
        if (!$apiKey && File::exists(base_path('.env'))) {
            $envContent = File::get(base_path('.env'));
            if (preg_match('/^GEMINI_API_KEY=(.*)$/m', $envContent, $matches)) {
                $apiKey = trim($matches[1], "\"' \r\n");
            }
        }

        if (!$apiKey) {
            return response()->json([
                'error' => 'Gemini AI API Key is not configured in .env file.'
            ], 200);
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

            $systemInstruction = "You are an expert image processing AI. Your role is to translate natural language photo adjustment requests into exact CSS/HTML5 Canvas filter values.\n" .
                                "You must output a raw, valid JSON object with the following keys and integer/float values ONLY. Do NOT wrap inside markdown blocks (such as ```json), and do NOT write any other text.\n" .
                                "Allowed Keys and Ranges:\n" .
                                "- brightness: integer percentage (e.g. 100 is original, 120 is brighter, 80 is darker)\n" .
                                "- contrast: integer percentage (e.g. 100 is original, 115 is higher contrast, 90 is softer contrast)\n" .
                                "- saturate: integer percentage (e.g. 100 is original, 140 is more vibrant, 70 is muted colors)\n" .
                                "- sepia: integer percentage from 0 to 100 (e.g. 0 is original, 25 for subtle warm/vintage tone)\n" .
                                "- hue_rotate: integer degrees from 0 to 360 (default 0)\n" .
                                "- grayscale: integer percentage from 0 to 100 (default 0, use 100 for absolute B&W)\n" .
                                "- invert: integer percentage from 0 to 100 (default 0)\n" .
                                "- blur: integer pixel value from 0 to 10 (default 0, keep at 0 unless user explicitly asks to blur the image)\n\n" .
                                "Rules of Thumb:\n" .
                                "- 'fix lighting' or 'brighten' -> brightness around 115-130, contrast around 105-110\n" .
                                "- 'warm tone' or 'cozy look' -> sepia around 15-25, saturate around 110, brightness around 105\n" .
                                "- 'vibrant colors' or 'pop' -> saturate around 140, contrast around 110\n" .
                                "- 'retro' or 'vintage' -> sepia around 30, contrast around 90, brightness around 105\n" .
                                "- 'darken' or 'cinematic' -> brightness around 85, contrast around 115\n" .
                                "- Output the JSON immediately without formatting characters, prefixes, or suffixes.";

            $response = Http::timeout(20)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $systemInstruction . "\n\nUser request: \"" . $prompt . "\""]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                ]
            ]);

            if ($response->successful()) {
                $resData = $response->json();
                if (isset($resData['candidates'][0]['content']['parts'][0]['text'])) {
                    $jsonText = trim($resData['candidates'][0]['content']['parts'][0]['text']);
                    
                    // Extra resilient parsing: extract substring between first { and last }
                    $firstBracket = strpos($jsonText, '{');
                    $lastBracket = strrpos($jsonText, '}');
                    if ($firstBracket !== false && $lastBracket !== false) {
                        $jsonText = substr($jsonText, $firstBracket, $lastBracket - $firstBracket + 1);
                    }
                    
                    $parameters = json_decode($jsonText, true);

                    if (is_array($parameters) && count($parameters) > 0) {
                        $normalized = [];
                        foreach ($parameters as $key => $val) {
                            $cleanKey = str_replace('-', '_', strtolower($key));
                            // Only allow valid slider keys
                            if (in_array($cleanKey, ['brightness', 'contrast', 'saturate', 'sepia', 'grayscale', 'invert', 'hue_rotate', 'blur'])) {
                                $normalized[$cleanKey] = (int)$val;
                            }
                        }

                        if (count($normalized) > 0) {
                            return response()->json([
                                'success' => true,
                                'filters' => $normalized
                            ]);
                        }
                    }
                    
                    Log::warning('Gemini output was not valid filters JSON: ' . $jsonText);
                }
            }

            return response()->json([
                'error' => 'Failed to calculate adjustments via AI. Please try a different prompt or adjust sliders manually.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Gallery AI Adjust error: ' . $e->getMessage());
            return response()->json(['error' => 'AI adjust failed: ' . $e->getMessage()], 200);
        }
    }
}
