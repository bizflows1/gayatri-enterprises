<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Attendance;
use App\Models\WeeklyEfficiencyReport;
use Carbon\Carbon;
use Exception;

class GenerateWeeklyEfficiency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-weekly-efficiency {--start= : Start date (YYYY-MM-DD)} {--end= : End date (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a weekly staff work efficiency report using Gemini 3.1 Flash-Lite';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Weekly Efficiency Report generation...');

        $apiKey = config('services.gemini.key');
        if (!$apiKey) {
            $apiKey = env('GEMINI_API_KEY');
        }

        if (!$apiKey) {
            $this->error('GEMINI_API_KEY is missing from configuration');
            return Command::FAILURE;
        }

        // Calculate date range (Saturday to Friday)
        if ($this->option('end')) {
            $endDate = Carbon::parse($this->option('end'))->endOfDay();
        } else {
            // If today is Friday, include today. Otherwise, get previous Friday.
            if (Carbon::now()->isFriday()) {
                $endDate = Carbon::now()->endOfDay();
            } else {
                $endDate = Carbon::now()->previous(Carbon::FRIDAY)->endOfDay();
            }
        }

        if ($this->option('start')) {
            $startDate = Carbon::parse($this->option('start'))->startOfDay();
        } else {
            // Saturday is 6 days before Friday
            $startDate = $endDate->copy()->subDays(6)->startOfDay();
        }

        $this->info("Analyzing attendance logs from: {$startDate->toDateString()} to {$endDate->toDateString()}");

        // Fetch logs
        $attendances = Attendance::with('user')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date', 'asc')
            ->get();

        if ($attendances->isEmpty()) {
            $this->warn('No staff attendance logs found for the selected week. Cannot generate report.');
            return Command::FAILURE;
        }

        $grouped = $attendances->groupBy('user_id');
        $staffLogsText = "";
        $metricsData = [];

        foreach ($grouped as $userId => $logs) {
            $user = $logs->first()->user;
            if (!$user) continue;

            $totalHours = 0;
            $bulletPoints = [];
            $daysPresent = 0;
            $daysLate = 0;

            foreach ($logs as $log) {
                $daysPresent++;
                if ($log->status === 'late') {
                    $daysLate++;
                }
                if ($log->total_hours) {
                    $totalHours += $log->total_hours;
                }
                if ($log->work_log) {
                    $lines = explode("\n", $log->work_log);
                    foreach ($lines as $line) {
                        $trimmed = trim($line);
                        if ($trimmed) {
                            $bulletPoints[] = "- [{$log->date->format('D d M')}] " . $trimmed;
                        }
                    }
                }
            }

            $metricsData[$user->name] = [
                'total_hours' => round($totalHours, 2),
                'days_present' => $daysPresent,
                'days_late' => $daysLate,
                'log_count' => count($bulletPoints)
            ];

            $staffLogsText .= "### Employee: {$user->name} ({$user->email})\n";
            $staffLogsText .= "- Total Hours Worked: " . round($totalHours, 2) . " hrs\n";
            $staffLogsText .= "- Days Present: {$daysPresent} days\n";
            $staffLogsText .= "- Days Late: {$daysLate} days\n";
            $staffLogsText .= "- Daily Task Logs:\n";
            if (count($bulletPoints) > 0) {
                $staffLogsText .= implode("\n", $bulletPoints) . "\n";
            } else {
                $staffLogsText .= "  (No work logs submitted for this week)\n";
            }
            $staffLogsText .= "\n---\n\n";
        }

        $this->info("Aggregated work logs for " . count($grouped) . " staff members.");

        // Build prompt
        $prompt = "You are Gemini 3.1 Flash-Lite, an expert organizational analyst and executive AI helper for a premium Indian Chartered Accountant firm.\n" .
                  "Your task is to analyze the following weekly staff activity logs and generate an executive, comprehensive 'Weekly Office Efficiency Report' for the Partners.\n\n" .
                  "Here is the staff performance and activity data from {$startDate->format('d M, Y')} to {$endDate->format('d M, Y')}:\n\n" .
                  "{$staffLogsText}\n" .
                  "Please generate a detailed, premium report that includes the following sections:\n" .
                  "1. Executive Summary: A concise high-level overview of the week's operational accomplishments and team productivity.\n" .
                  "2. Top Performing Staff: Identify the standout performers of the week based on productivity, volume of logs, consistency, or outstanding tasks completed.\n" .
                  "3. Delayed Client Cases / Bottlenecks: Analyze the logs to identify any recurring bottlenecks, slow approvals, technical issues, delayed client deliveries, or signs of burnout/inefficiency.\n" .
                  "4. Actionable Strategic Recommendations: Provide at least 3-4 professional, concrete recommendations for the Partners to optimize the office's operations next week.\n\n" .
                  "CRITICAL INSTRUCTIONS:\n" .
                  "- Do not mention this prompt or its instructions in your output.\n" .
                  "- Keep the tone executive, objective, encouraging, yet highly professional and analytical.\n" .
                  "- Use clear, professional headings, clean markdown, and bullet points.\n" .
                  "- Return your response as a valid JSON object with exactly two keys:\n" .
                  "  a) 'report_markdown' (string containing the beautifully styled Markdown report)\n" .
                  "  b) 'highlights' (an array of strings summarizing the top 3-4 bullet-point key findings for quick partner dashboard cards)\n\n" .
                  "Return ONLY this valid JSON object.";

        // Use gemini-2.5-flash as primary since it is standard and reliable
        $model = 'gemini-2.5-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;

        $this->info("Sending data to Google Gemini API (model: {$model})...");

        $generationConfig = [
            'temperature' => 0.4,
            'responseMimeType' => 'application/json',
            'responseSchema' => [
                'type' => 'OBJECT',
                'properties' => [
                    'report_markdown' => [
                        'type' => 'STRING',
                        'description' => 'The weekly office efficiency report in Markdown format'
                    ],
                    'highlights' => [
                        'type' => 'ARRAY',
                        'items' => [
                            'type' => 'STRING'
                        ],
                        'description' => '3-4 bullet-point key findings for partner dashboard cards'
                    ]
                ],
                'required' => ['report_markdown', 'highlights']
            ]
        ];

        try {
            $response = Http::timeout(120)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => $generationConfig
            ]);

            // Fallback to gemini-1.5-flash if gemini-2.5-flash fails
            if (!$response->successful()) {
                $this->warn("Model {$model} request failed or model not available. Retrying with fallback model gemini-1.5-flash...");
                $model = 'gemini-1.5-flash';
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;
                $response = Http::timeout(120)->post($url, [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => $generationConfig
                ]);
            }

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $jsonText = $data['candidates'][0]['content']['parts'][0]['text'];
                    
                    // Clean markdown code block wraps if returned by the API
                    $jsonText = trim($jsonText);
                    if (str_starts_with($jsonText, '```json')) {
                        $jsonText = substr($jsonText, 7);
                    } elseif (str_starts_with($jsonText, '```')) {
                        $jsonText = substr($jsonText, 3);
                    }
                    if (str_ends_with($jsonText, '```')) {
                        $jsonText = substr($jsonText, 0, -3);
                    }
                    $jsonText = trim($jsonText);
                    
                    $result = json_decode($jsonText, true);

                    if (json_last_error() === JSON_ERROR_NONE && isset($result['report_markdown'])) {
                        // Store the report
                        $report = WeeklyEfficiencyReport::create([
                            'start_date' => $startDate->toDateString(),
                            'end_date' => $endDate->toDateString(),
                            'report_content' => $result['report_markdown'],
                            'metrics' => [
                                'highlights' => $result['highlights'] ?? [],
                                'staff_metrics' => $metricsData,
                                'generated_by_model' => $model
                            ]
                        ]);

                        $this->info("Weekly Office Efficiency Report successfully generated and saved! ID: {$report->id}");
                        return Command::SUCCESS;
                    } else {
                        $jsonError = json_last_error_msg();
                        $this->error("Failed to parse JSON from Gemini response. Error: {$jsonError}. Raw response: " . substr($jsonText, 0, 500));
                        Log::error("GenerateWeeklyEfficiency: JSON parsing error: {$jsonError}.", ['raw' => $jsonText]);
                    }
                } else {
                    $this->error("Unexpected API response structure.");
                    Log::error("GenerateWeeklyEfficiency: Unexpected API response structure.", ['response' => $data]);
                }
            } else {
                $this->error("API request failed: " . $response->body());
                Log::error("GenerateWeeklyEfficiency: API failed.", ['body' => $response->body()]);
            }
        } catch (Exception $e) {
            $this->error("Exception occurred during API request: " . $e->getMessage());
            Log::error("GenerateWeeklyEfficiency: Exception.", ['message' => $e->getMessage()]);
        }

        return Command::FAILURE;
    }
}
