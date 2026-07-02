<?php

namespace App\Http\Controllers;

use App\Models\SiteNotice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoticeController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->role === 'admin', 403);
        $notices = SiteNotice::latest()->paginate(15);
        $users = User::where('role', 'client')->orderBy('name')->get();
        return view('admin.notices.index', compact('notices', 'users'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,warning,urgent',
            'target_type' => 'required|in:all,specific,staff,specific_staff',
            'user_ids' => 'required_if:target_type,specific|required_if:target_type,specific_staff|array',
            'expires_at' => 'nullable|date',
        ]);

        $expiresAt = $request->expires_at ?: now()->addWeek();

        $targetType = $request->target_type === 'specific_staff' ? 'staff' : $request->target_type;

        try {
            \DB::beginTransaction();
            $notice = SiteNotice::create([
                'title' => $request->title,
                'content' => $request->content,
                'type' => $request->type,
                'target_type' => $targetType,
                'expires_at' => $expiresAt,
                'is_active' => true,
            ]);

            // Attach users if targeted
            if ($request->target_type === 'specific' || $request->target_type === 'specific_staff') {
                if (!empty($request->user_ids)) {
                    $notice->users()->attach($request->user_ids);
                }
            } elseif ($request->target_type === 'staff') {
                $staffIds = User::where('role', 'staff')->pluck('id');
                if ($staffIds->isNotEmpty()) {
                    $notice->users()->attach($staffIds);
                }
            } elseif ($request->target_type === 'all') {
                // Attach ALL clients + staff so read-tracking works via pivot table
                $allUserIds = User::whereIn('role', ['client', 'staff'])->pluck('id');
                if ($allUserIds->isNotEmpty()) {
                    $notice->users()->attach($allUserIds);
                }
            }
            \DB::commit();

            // Send Push Notifications
            $this->sendPushNotifications($notice, $request->target_type, $request->user_ids ?? []);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error("Notice Broadcast Critical Failure: " . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Broadcast Error: ' . $e->getMessage());
        }

        return redirect()->route('admin.notices')->with('success', 'Professional notice broadcasted successfully!');
    }

    public function destroy($id)
    {
        abort_unless(Auth::user()->role === 'admin', 403);
        $notice = SiteNotice::findOrFail($id);
        $notice->delete();
        return back()->with('success', 'Notice deleted successfully.');
    }

    public function markAsRead($id)
    {
        $user = Auth::user();

        // Use syncWithoutDetaching to create or update the pivot record effortlessly
        $user->notices()->syncWithoutDetaching([
            $id => [
                'is_read' => true,
                'read_at' => now()
            ]
        ]);

        return response()->json(['status' => 'success']);
    }
    protected function sendPushNotifications($notice, $targetType, $userIds)
    {
        try {
            $query = User::query();
            if ($targetType === 'specific') {
                $query->whereIn('id', $userIds);
            } elseif ($targetType === 'staff') {
                $query->where('role', 'staff');
            } elseif ($targetType === 'all') {
                $query->whereIn('role', ['client', 'staff']);
            }
            
            $users = $query->with('pushSubscriptions')->get();
            $payload = json_encode([
                'title' => 'New Notice: ' . $notice->title,
                'body' => strip_tags($notice->content),
                'icon' => '/pwa-icon.png',
                'url' => route('client.dashboard'),
                // 'sound' => disabled
            ]);

            foreach ($users as $user) {
                foreach ($user->pushSubscriptions as $sub) {
                    $this->dispatchPush($sub, $payload);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Notice Push Error: ' . $e->getMessage());
        }
    }

    protected function dispatchPush($sub, $payload)
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
                        \Log::warning("Notice Push Failed: " . $report->getReason());
                        if ($report->isSubscriptionExpired()) {
                            \App\Models\PushSubscription::where('endpoint', $report->getEndpoint())->delete();
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Push sending failed: ' . $e->getMessage());
            }
        }
    }
}
