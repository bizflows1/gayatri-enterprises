<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Dispatch a Web Push Notification to a list of users or a single user.
     *
     * @param mixed $users
     * @param string $title
     * @param string $body
     * @param string|null $url
     * @param string|null $sound
     */
    public static function sendToUsers($users, string $title, string $body, ?string $url = null, ?string $sound = null)
    {
        if (is_array($users)) {
            $users = collect($users);
        } elseif ($users instanceof User) {
            $users = collect([$users]);
        } elseif ($users instanceof \Illuminate\Database\Eloquent\Collection) {
            // Already a collection
        } else {
            $users = collect([$users]);
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'icon' => '/pwa-icon.png',
            'url' => $url ?: route('client.dashboard'),
            // 'sound' => disabled
        ]);

        foreach ($users as $user) {
            if (!$user) continue;
            // Load relations if not already loaded to avoid N+1 issues
            if (!$user->relationLoaded('pushSubscriptions')) {
                $user->load('pushSubscriptions');
            }
            foreach ($user->pushSubscriptions as $sub) {
                self::dispatchPush($sub, $payload);
            }
        }
    }

    /**
     * Internal low-level push dispatcher.
     */
    public static function dispatchPush($sub, string $payload)
    {
        if (class_exists('\Minishlink\WebPush\WebPush')) {
            try {
                $auth = [
                    'VAPID' => [
                        'subject' => 'mailto:info@gayatrient.com',
                        'publicKey' => config('webpush.vapid.public_key') ?: env('VAPID_PUBLIC_KEY'),
                        'privateKey' => config('webpush.vapid.private_key') ?: env('VAPID_PRIVATE_KEY'),
                    ],
                ];
                
                $webPush = new \Minishlink\WebPush\WebPush($auth);
                $webPush->queueNotification(
                    \Minishlink\WebPush\Subscription::create([
                        'endpoint' => $sub->endpoint,
                        'publicKey' => $sub->public_key,
                        'authToken' => $sub->auth_token,
                        'contentEncoding' => $sub->content_encoding ?: 'aesgcm',
                    ]),
                    $payload
                );
                
                foreach ($webPush->flush() as $report) {
                    if (!$report->isSuccess()) {
                        Log::warning("WebPush Service: Notification failed: " . $report->getReason());
                    }
                }
            } catch (\Exception $e) {
                Log::error("WebPush Service Error: " . $e->getMessage());
            }
        } else {
            Log::warning("WebPush Service: Minishlink\\WebPush\\WebPush class does not exist.");
        }
    }
}
