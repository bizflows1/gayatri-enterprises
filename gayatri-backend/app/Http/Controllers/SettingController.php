<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{


    /**
     * Display the advanced settings form.
     */
    public function advanced()
    {
        abort_unless(Auth::check() && Auth::user()->role === 'admin', 403, 'Unauthorized access.');

        $settings = [
            'ai_image_editor' => env('AI_IMAGE_EDITOR_ENABLED', true),
            'ai_acts' => env('AI_ACTS_ENABLED', true),
            'ai_services' => env('AI_SERVICES_ENABLED', true),
            'ai_assistant' => env('AI_ASSISTANT_ENABLED', true),
            'gemini_api_key' => env('GEMINI_API_KEY', ''),
            'filesystem_disk' => env('FILESYSTEM_DISK', 'public'),
            
            // Wasabi Storage Details
            'wasabi_key' => env('WASABI_ACCESS_KEY_ID', ''),
            'wasabi_secret' => env('WASABI_SECRET_ACCESS_KEY', ''),
            'wasabi_region' => env('WASABI_DEFAULT_REGION', 'ap-southeast-1'),
            'wasabi_bucket' => env('WASABI_BUCKET', ''),
            'wasabi_endpoint' => env('WASABI_ENDPOINT', 'https://s3.ap-southeast-1.wasabisys.com'),

            // OneDrive Details
            'onedrive_enabled' => env('ONEDRIVE_BACKUP_ENABLED', false),
            'onedrive_client_id' => env('ONEDRIVE_CLIENT_ID', ''),
            'onedrive_client_secret' => env('ONEDRIVE_CLIENT_SECRET', ''),
            'onedrive_tenant_id' => env('ONEDRIVE_TENANT_ID', ''),
            'onedrive_redirect_uri' => env('ONEDRIVE_REDIRECT_URI', ''),
        ];

        return view('admin.settings.advanced', compact('settings'));
    }

    /**
     * Update advanced settings in the .env file.
     */
    public function updateAdvanced(Request $request)
    {
        abort_unless(Auth::check() && Auth::user()->role === 'admin', 403, 'Unauthorized access.');

        $request->validate([
            'gemini_api_key' => 'nullable|string',
            'filesystem_disk' => 'required|in:public,wasabi',
            'wasabi_key' => 'required_if:filesystem_disk,wasabi|nullable|string',
            'wasabi_secret' => 'required_if:filesystem_disk,wasabi|nullable|string',
            'wasabi_region' => 'required_if:filesystem_disk,wasabi|nullable|string',
            'wasabi_bucket' => 'required_if:filesystem_disk,wasabi|nullable|string',
            'wasabi_endpoint' => 'required_if:filesystem_disk,wasabi|nullable|string',
            
            'onedrive_client_id' => 'required_if:onedrive_enabled,1|nullable|string',
            'onedrive_client_secret' => 'required_if:onedrive_enabled,1|nullable|string',
            'onedrive_tenant_id' => 'required_if:onedrive_enabled,1|nullable|string',
            'onedrive_redirect_uri' => 'required_if:onedrive_enabled,1|nullable|string',
        ]);

        $envUpdates = [
            'AI_IMAGE_EDITOR_ENABLED' => $request->has('ai_image_editor') ? 'true' : 'false',
            'AI_ACTS_ENABLED' => $request->has('ai_acts') ? 'true' : 'false',
            'AI_SERVICES_ENABLED' => $request->has('ai_services') ? 'true' : 'false',
            'AI_ASSISTANT_ENABLED' => $request->has('ai_assistant') ? 'true' : 'false',
            'GEMINI_API_KEY' => $request->input('gemini_api_key') ?? '',
            'FILESYSTEM_DISK' => $request->input('filesystem_disk'),
            
            // Wasabi
            'WASABI_ACCESS_KEY_ID' => $request->input('wasabi_key') ?? '',
            'WASABI_SECRET_ACCESS_KEY' => $request->input('wasabi_secret') ?? '',
            'WASABI_DEFAULT_REGION' => $request->input('wasabi_region') ?? 'ap-southeast-1',
            'WASABI_BUCKET' => $request->input('wasabi_bucket') ?? '',
            'WASABI_ENDPOINT' => $request->input('wasabi_endpoint') ?? 'https://s3.ap-southeast-1.wasabisys.com',

            // OneDrive
            'ONEDRIVE_BACKUP_ENABLED' => $request->has('onedrive_enabled') ? 'true' : 'false',
            'ONEDRIVE_CLIENT_ID' => $request->input('onedrive_client_id') ?? '',
            'ONEDRIVE_CLIENT_SECRET' => $request->input('onedrive_client_secret') ?? '',
            'ONEDRIVE_TENANT_ID' => $request->input('onedrive_tenant_id') ?? '',
            'ONEDRIVE_REDIRECT_URI' => $request->input('onedrive_redirect_uri') ?? '',
        ];

        try {
            $this->updateEnv($envUpdates);
            
            // Clear config cache to apply .env changes immediately in Laravel
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            return redirect()->route('admin.settings.advanced')->with('success', 'Advanced settings updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.settings.advanced')->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Helper to write values into .env file securely.
     */
    protected function updateEnv(array $data)
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            throw new \Exception('.env file not found.');
        }

        $content = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            // Trim whitespace and outer quotes if exist, to standardize input
            $value = trim($value);
            
            // Quote the value if it has spaces or special characters and is not already quoted
            if (preg_match('/\s/', $value) && !preg_match('/^".*"$/', $value)) {
                $value = '"' . $value . '"';
            }

            $keyPattern = "/^" . preg_quote($key, '/') . "=.*/m";

            if (preg_match($keyPattern, $content)) {
                $content = preg_replace($keyPattern, "{$key}={$value}", $content);
            } else {
                // If key does not exist, append it
                $content .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $content);
    }
}
