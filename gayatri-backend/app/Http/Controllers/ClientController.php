<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Folder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Mail\ClientPasswordNotification;

class ClientController extends Controller
{
    // ==========================================
    // CLIENT MANAGEMENT
    // ==========================================

    public function index(Request $request)
    {
        $this->authorizeClients();

        $search     = $request->search ?? '';
        $roleFilter = $request->role_filter ?? '';

        $query = User::query();

        // SECURITY: Staff can ONLY see Clients. Admins can see everyone.
        if (Auth::user()->role === 'staff') {
            $query->where('role', 'client');
        }

        if ($roleFilter) {
            $query->where('role', $roleFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }

        // Calculate statistics counts of all matched records BEFORE applying pagination
        $totalCount      = $query->count();
        $activeCount     = (clone $query)->where('is_active', true)->count();
        $blockedCount    = (clone $query)->where('is_active', false)->count();
        $thisMonthCount  = (clone $query)->where('created_at', '>=', now()->startOfMonth())->count();

        // Order by recently added and paginate to 10 records per page
        $users = $query->latest()->paginate(10)->withQueryString();

        return view('admin.manage-clients', compact(
            'users',
            'search',
            'totalCount',
            'activeCount',
            'blockedCount',
            'thisMonthCount'
        ));
    }

    public function create()
    {
        $this->authorizeClients();
        return view('admin.add-user');
    }

    public function store(Request $request)
    {
        $this->authorizeClients();

        $request->validate([
            'name' => 'required',
            'phone' => 'required|digits:10|unique:users,phone',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:client,staff,admin',
        ]);

        // SECURITY: Only Admin can create new Admin users
        if ($request->role === 'admin' && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // SECURITY: Staff can ONLY create Client accounts
        if (Auth::user()->role === 'staff' && $request->role !== 'client') {
            abort(403, 'Unauthorized: Staff can only create Client accounts.');
        }

        $plainPassword = $request->filled('password') ? $request->password : Str::random(10);
        $password = Hash::make($plainPassword);
        $permissions = null;

        if (in_array($request->role, ['staff', 'admin'])) {
            // Require password if it's manual, or ensure generated one exists (logic simplified below)
            if (!$request->filled('password')) {
                // For staff/admin, we usually mandate manual passwords, but if we allow generated:
                 // Validation was already handled by UI or logic above.
            }
            $permissions = $request->permissions ? json_encode($request->permissions) : null;
        }

        $user = new User([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => $password,
            'permissions' => $permissions,
            'date_of_birth' => $request->date_of_birth,
            'anniversary_date' => $request->anniversary_date,
        ]);
        $user->role = $request->role;
        $user->is_active = true;
        
        if ($request->role === 'client') {
            $user->bank_name = $request->bank_name;
            $user->bank_account_number = $request->bank_account_number;
            $user->bank_ifsc = $request->bank_ifsc;
            $user->upi_id = $request->upi_id;
        }

        $user->save();

        // Log the creation in general ActivityLog
        $currentUser = Auth::user();
        \App\Models\ActivityLog::create([
            'user_id' => $currentUser->id,
            'action' => ucfirst($user->role) . ' Added',
            'description' => ucfirst($currentUser->role) . ' ' . $currentUser->name . ' created new ' . ucfirst($user->role) . ': ' . $user->name . ' (' . $user->email . ')',
            'ip_address' => $request->ip()
        ]);

        // Create default folders for clients
        if ($request->role === 'client') {
            $this->createDefaultFolders($user->id);

            // AUTOMATIC CREDENTIALS EMAIL: Send ID & Password to client
            try {
                Mail::to($user->email)->send(new \App\Mail\ClientPasswordNotification($user, $plainPassword));
            } catch (\Exception $e) {
                \Log::error('Failed to send welcome email with credentials: ' . $e->getMessage());
            }
        }

        return redirect()->route('manage.clients')
            ->with('success', 'User Added Successfully!')
            ->with('generated_password', $plainPassword);
    }

    public function edit($id)
    {
        $this->authorizeClients();
        $user = User::findOrFail($id);

        // SECURITY: Staff can ONLY edit Clients.
        if (Auth::user()->role === 'staff' && $user->role !== 'client') {
            abort(403, 'Unauthorized: Staff can only manage client accounts.');
        }

        return view('admin.edit-user', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $this->authorizeClients();

        $user = User::findOrFail($id);

        // SECURITY: Strict role-based editing rules
        if (Auth::user()->role === 'staff') {
            // Staff can ONLY update Clients
            if ($user->role !== 'client') {
                abort(403, 'Unauthorized: Staff can only manage client accounts.');
            }
            // Staff CANNOT change a user's role (implicit in logic below as we don't update role for staff edits here, 
            // but let's be explicit if role was in request)
            if ($request->has('role') && $request->role !== 'client') {
                abort(403, 'Unauthorized: Staff cannot change user roles.');
            }
        } elseif (Auth::user()->role === 'admin') {
            // Admin can update anyone, but if they are updating another admin, that's fine.
            // (Optional: Prevent self-demotion or other logic if needed)
        }

        $request->validate([
            'name' => 'required',
            'phone' => 'required|digits:10|unique:users,phone,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'date_of_birth' => $request->date_of_birth,
            'anniversary_date' => $request->anniversary_date,
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($user->role === 'staff') {
            $user->permissions = $request->permissions ? json_encode($request->permissions) : null;
        }

        if ($user->role === 'client') {
            $user->gst_number = $request->gst_number;
            $user->pan_number = $request->pan_number;
            
            if (Auth::user()->role === 'admin') {
                $user->bank_name = $request->bank_name;
                $user->bank_account_number = $request->bank_account_number;
                $user->bank_ifsc = $request->bank_ifsc;
                $user->upi_id = $request->upi_id;
            }
        }

        $user->save();

        // Log the update in general ActivityLog
        $currentUser = Auth::user();
        \App\Models\ActivityLog::create([
            'user_id' => $currentUser->id,
            'action' => ucfirst($user->role) . ' Updated',
            'description' => ucfirst($currentUser->role) . ' ' . $currentUser->name . ' updated details for ' . ucfirst($user->role) . ': ' . $user->name . ' (' . $user->email . ')',
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('manage.clients')->with('success', 'User Updated!');
    }

    public function toggleStatus($id)
    {
        $this->authorizeClients();
        $user = User::findOrFail($id);
        
        // SECURITY: Staff can ONLY toggle Clients
        if (Auth::user()->role === 'staff' && $user->role !== 'client') {
            abort(403, 'Unauthorized action.');
        }

        // SECURITY: Only Admin can toggle Admin/Staff status
        if (in_array($user->role, ['admin', 'staff']) && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $user->is_active = !$user->is_active;
        
        // If activating, reset failed attempts
        if ($user->is_active) {
            $user->login_attempts = 0;
        }
        
        $user->save();

        // Log the status toggle in general ActivityLog
        $currentUser = Auth::user();
        $statusStr = $user->is_active ? 'Activated' : 'Suspended';
        \App\Models\ActivityLog::create([
            'user_id' => $currentUser->id,
            'action' => 'User Status Updated',
            'description' => ucfirst($currentUser->role) . ' ' . $currentUser->name . ' ' . strtolower($statusStr) . ' account for ' . ucfirst($user->role) . ': ' . $user->name,
            'ip_address' => request()->ip()
        ]);

        return back()->with('success', 'Status & Login Attempts Updated!');
    }

    public function destroy($id)
    {
        // 1. Authorization check
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admins can delete users.');
        }

        // 2. Prevent self-deletion
        if ($id == Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user = User::findOrFail($id);

        DB::beginTransaction();
        try {
            // 3. Log the action before deletion
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'User Deletion Started',
                'description' => 'Target User: ' . $user->name . ' (Email: ' . $user->email . ', Role: ' . $user->role . ')',
                'ip_address' => request()->ip()
            ]);

            // 4. Manual Relationship Cleanup (to bypass potential constraint issues)
            // Detach from Many-to-Many relationships
            $user->tasks()->detach();
            $user->notices()->detach();
            
            // Clean up chat relationships (Handling both migration and model names)
            try {
                if (Schema::hasTable('conversation_user') && Schema::hasColumn('conversation_user', 'user_id')) {
                    DB::table('conversation_user')->where('user_id', $user->id)->delete();
                }
            } catch (\Exception $e) {
                Log::warning("Legacy conversation_user cleanup skipped: " . $e->getMessage());
            }

            try {
                if (Schema::hasTable('team_conversation_user') && Schema::hasColumn('team_conversation_user', 'user_id')) {
                    DB::table('team_conversation_user')->where('user_id', $user->id)->delete();
                }
            } catch (\Exception $e) {
                Log::warning("team_conversation_user cleanup skipped: " . $e->getMessage());
            }

            // Handle conversations created by this user (nullify creator)
            try {
                if (Schema::hasTable('conversations') && Schema::hasColumn('conversations', 'created_by')) {
                    DB::table('conversations')->where('created_by', $user->id)->update(['created_by' => null]);
                }
            } catch (\Exception $e) {
                Log::warning("Legacy conversations cleanup skipped: " . $e->getMessage());
            }

            try {
                if (Schema::hasTable('team_conversations') && Schema::hasColumn('team_conversations', 'created_by')) {
                    DB::table('team_conversations')->where('created_by', $user->id)->update(['created_by' => null]);
                }
            } catch (\Exception $e) {
                Log::warning("team_conversations cleanup skipped: " . $e->getMessage());
            }
            
            // Clean up chat messages
            try {
                if (Schema::hasTable('messages') && Schema::hasColumn('messages', 'user_id')) {
                    DB::table('messages')->where('user_id', $user->id)->delete();
                }
            } catch (\Exception $e) {
                Log::warning("Legacy messages cleanup skipped: " . $e->getMessage());
            }

            try {
                if (Schema::hasTable('team_messages') && Schema::hasColumn('team_messages', 'user_id')) {
                    DB::table('team_messages')->where('user_id', $user->id)->delete();
                }
            } catch (\Exception $e) {
                Log::warning("team_messages cleanup skipped: " . $e->getMessage());
            }
            
            // Clean up read receipts
            try {
                if (Schema::hasTable('message_reads') && Schema::hasColumn('message_reads', 'user_id')) {
                    DB::table('message_reads')->where('user_id', $user->id)->delete();
                }
            } catch (\Exception $e) {
                Log::warning("Legacy message_reads cleanup skipped: " . $e->getMessage());
            }

            try {
                if (Schema::hasTable('team_message_reads') && Schema::hasColumn('team_message_reads', 'user_id')) {
                    DB::table('team_message_reads')->where('user_id', $user->id)->delete();
                }
            } catch (\Exception $e) {
                Log::warning("team_message_reads cleanup skipped: " . $e->getMessage());
            }

            // Clean up push subscriptions
            $user->pushSubscriptions()->delete();

            // 5. Delete the User
            $user->delete();

            DB::commit();
            return back()->with('success', 'User and all related records deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User deletion failed for ID ' . $id . ': ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to delete user. Technical Details: ' . $e->getMessage());
        }
    }

    // HELPER - Create Default Folders for Client
    private function createDefaultFolders($userId)
    {
        // Specific years requested by user + KYC
        $folders = [
            '2023-2024',
            '2024-2025',
            '2025-2026',
            '2026-2027',
            'KYC Docs'
        ];

        $user = User::findOrFail($userId);
        
        // Safe Name for Folder Path
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $user->name);
        
        foreach ($folders as $folderName) {
            Folder::firstOrCreate([
                'user_id' => $userId,
                'name' => $folderName,
                'parent_id' => null,
                'path' => $folderName
            ]);

            // Create empty folder in Wasabi to initialize structure
            // clients/{id}_{name}/{folderName}/
            $wasabiPath = 'clients/' . $userId . '_' . $safeName . '/' . $folderName;
            $disk = config('filesystems.default');
            Storage::disk($disk)->put($wasabiPath . '/.folder', '');
        }
    }

    // ==========================================
    // BULK CLIENT & CREDENTIALS CSV IMPORT
    // ==========================================

    public function import(Request $request)
    {
        $this->authorizeClients();

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to open uploaded CSV file.'
            ], 422);
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return response()->json([
                'success' => false,
                'message' => 'Empty CSV file uploaded.'
            ], 422);
        }

        // Clean & map headers
        $headers = array_map(function($h) {
            return strtolower(trim(str_replace(["\xEF\xBB\xBF", '"', "'"], '', $h)));
        }, $headers);

        $headerMap = [];
        foreach ($headers as $index => $header) {
            $headerMap[$header] = $index;
        }

        // Check if mandatory headers exist
        if (!isset($headerMap['name']) || !isset($headerMap['email'])) {
            fclose($handle);
            return response()->json([
                'success' => false,
                'message' => 'Mandatory columns "name" and "email" are missing in the CSV headers.'
            ], 422);
        }

        // Dynamically identify all portals available in the CSV headers
        $dynamicPortals = [];
        foreach ($headerMap as $headerKey => $headerIndex) {
            if (preg_match('/^(.*)_(login|username)$/', $headerKey, $matches)) {
                $portalPrefix = $matches[1];
                if (!isset($dynamicPortals[$portalPrefix])) {
                    $dynamicPortals[$portalPrefix] = ['login' => null, 'password' => null];
                }
                $dynamicPortals[$portalPrefix]['login'] = $headerKey;
            } elseif (preg_match('/^(.*)_password$/', $headerKey, $matches)) {
                $portalPrefix = $matches[1];
                if (!isset($dynamicPortals[$portalPrefix])) {
                    $dynamicPortals[$portalPrefix] = ['login' => null, 'password' => null];
                }
                $dynamicPortals[$portalPrefix]['password'] = $headerKey;
            }
        }
        
        // Filter: only keep portals having both a username/login AND a password column
        $dynamicPortals = array_filter($dynamicPortals, function($info) {
            return !empty($info['login']) && !empty($info['password']);
        });

        $totalRows = 0;
        $createdClients = 0;
        $updatedClients = 0;
        $skippedRows = 0;
        $logs = [];

        $val = function($key, $row) use ($headerMap) {
            if (!isset($headerMap[$key])) return null;
            $idx = $headerMap[$key];
            return isset($row[$idx]) ? trim($row[$idx]) : null;
        };

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                // Skip completely empty rows
                if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                    continue;
                }

                $totalRows++;
                $name = $val('name', $row);
                $email = $val('email', $row);
                
                // 1. Validations
                if (empty($name) || empty($email)) {
                    $logs[] = "[ERROR] Row {$totalRows}: Name and Email are mandatory fields. Skipping row.";
                    $skippedRows++;
                    continue;
                }

                // Clean spaces in email
                $emailClean = preg_replace('/\s+/', '', $email);
                if (!filter_var($emailClean, FILTER_VALIDATE_EMAIL)) {
                    $logs[] = "[ERROR] Row {$totalRows}: Invalid email format '{$email}'. Skipping row.";
                    $skippedRows++;
                    continue;
                }

                // Normalize name
                $nameNormalized = ucwords(strtolower($name));

                // Process dob and anniversary
                $dob = $val('date_of_birth', $row);
                $anniv = $val('anniversary_date', $row);
                $dobParsed = null;
                $annivParsed = null;
                try {
                    if (!empty($dob)) $dobParsed = \Carbon\Carbon::parse($dob)->format('Y-m-d');
                    if (!empty($anniv)) $annivParsed = \Carbon\Carbon::parse($anniv)->format('Y-m-d');
                } catch (\Exception $e) {}

                // Normalize phone
                $phone = $val('phone', $row);
                $cleanPhone = null;
                if (!empty($phone)) {
                    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                    if (strlen($cleanPhone) >= 10) {
                        $cleanPhone = substr($cleanPhone, -10);
                    } else {
                        $logs[] = "[WARNING] Row {$totalRows}: Phone number '{$phone}' is invalid (must be 10 digits). Creating/Updating without phone.";
                        $cleanPhone = null;
                    }
                } else {
                    $logs[] = "[WARNING] Row {$totalRows}: Phone number is missing. Client will be created/updated without phone.";
                }

                // Check duplicate
                $user = null;
                if ($cleanPhone) {
                    $user = User::where('email', $emailClean)->orWhere('phone', $cleanPhone)->first();
                } else {
                    $user = User::where('email', $emailClean)->first();
                }

                $isNew = false;
                $plainPassword = '';

                if ($user) {
                    if ($user->role !== 'client') {
                        $logs[] = "[ERROR] Row {$totalRows}: User with email/phone already exists but is a " . strtoupper($user->role) . ". Cannot overwrite. Skipping row.";
                        $skippedRows++;
                        continue;
                    }
                    $logs[] = "[INFO] Row {$totalRows}: Client '{$user->name}' already exists (Email: {$emailClean}). Skipping user creation.";
                    $updatedClients++;
                } else {
                    $isNew = true;
                    $plainPassword = Str::random(10);
                    
                    if ($request->boolean('dry_run', false)) {
                        $logs[] = "[SIMULATION] Row {$totalRows}: Will create new client '{$nameNormalized}' and auto-generate credentials.";
                        $createdClients++;
                    } else {
                        $user = new User([
                            'name' => $nameNormalized,
                            'email' => $emailClean,
                            'phone' => $cleanPhone,
                            'password' => Hash::make($plainPassword),
                            'date_of_birth' => $dobParsed,
                            'anniversary_date' => $annivParsed,
                        ]);
                        $user->role = 'client';
                        $user->is_active = true;
                        $user->save();

                        // Create Folders
                        $this->createDefaultFolders($user->id);

                        // General Activity Log
                        \App\Models\ActivityLog::create([
                            'user_id' => Auth::id(),
                            'action' => 'Client Added',
                            'description' => 'Import engine created new Client ' . $user->name . ' (' . $user->email . ')',
                            'ip_address' => $request->ip()
                        ]);

                        if ($request->boolean('welcome_email', true)) {
                            try {
                                Mail::to($user->email)->send(new \App\Mail\ClientPasswordNotification($user, $plainPassword));
                                $logs[] = "[SUCCESS] Row {$totalRows}: Created client '{$nameNormalized}', generated password, and sent welcome email.";
                            } catch (\Exception $e) {
                                $logs[] = "[SUCCESS] Row {$totalRows}: Created client '{$nameNormalized}' but failed to send welcome email: " . $e->getMessage();
                            }
                        } else {
                            $logs[] = "[SUCCESS] Row {$totalRows}: Created client '{$nameNormalized}' (welcome email disabled).";
                        }
                        $createdClients++;
                    }
                }

                // 4. Bank Details processing
                $bank_name = $val('bank_name', $row);
                $bank_account_number = $val('bank_account_number', $row);
                $bank_ifsc = $val('bank_ifsc', $row);
                $upi_id = $val('upi_id', $row);

                if (!empty($bank_name) || !empty($bank_account_number) || !empty($bank_ifsc) || !empty($upi_id)) {
                    if ($request->boolean('dry_run', false)) {
                        $logs[] = "[SIMULATION] Row {$totalRows}: Will save Bank details.";
                    } else {
                        $user->bank_name = !empty($bank_name) ? $bank_name : $user->bank_name;
                        $user->bank_account_number = !empty($bank_account_number) ? $bank_account_number : $user->bank_account_number;
                        $user->bank_ifsc = !empty($bank_ifsc) ? $bank_ifsc : $user->bank_ifsc;
                        $user->upi_id = !empty($upi_id) ? $upi_id : $user->upi_id;
                        $user->save();
                        $logs[] = "[SUCCESS] Row {$totalRows}: Updated Bank details.";
                    }
                }
            }

            if ($request->boolean('dry_run', false)) {
                DB::rollBack();
                $logs[] = "[INFO] Dry run simulation finished. Database rollback committed successfully.";
            } else {
                DB::commit();
                $logs[] = "[INFO] Bulk Import process completed successfully.";
            }

            fclose($handle);

            return response()->json([
                'success' => true,
                'total_rows' => $totalRows,
                'created_count' => $createdClients,
                'updated_count' => $updatedClients,
                'skipped_count' => $skippedRows,
                'logs' => $logs
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during parsing: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadSample()
    {
        $this->authorizeClients();

        $headers = [
            'name', 'email', 'phone', 'date_of_birth', 'anniversary_date',
            'gst_login', 'gst_password', 
            'income_tax_login', 'income_tax_password', 
            'eway_login', 'eway_password',
            'traces_login', 'traces_password', 
            'mca_login', 'mca_password',
            'paam_login', 'paam_password',
            'steelcity_login', 'steelcity_password',
            'bank_name', 'bank_account_number', 'bank_ifsc', 'upi_id'
        ];

        $rows = [
            [
                'Ramesh Kumar', 'ramesh@example.com', '9876543210', '1990-05-15', '',
                'RAMESH_GST', 'GstPass@123', 'RAMESH_ITR', 'ItrPass@456', 
                'RAMESH_EWAY', 'EwayPass@789', 
                '', '', '', '', 
                '', '', '', '',
                'HDFC Bank', '1234567890', 'HDFC0001234', 'ramesh@upi'
            ],
            [
                'Sita Sharma', 'sita@example.com', '9988776655', '', '2015-12-01',
                'SITA_GST', 'SitaGst#99', '', '', 
                '', '', 
                'SITA_TRACES', 'TracesPass!', 'SITA_MCA', 'McaPass$789', 
                'SITA_PAAM', 'PaamPass$1', 'SITA_STEELCITY', 'SteelCityPass$2',
                'ICICI Bank', '0987654321', 'ICIC0000987', 'sita@icici'
            ],
            [
                'Aditi Roy', 'aditi@example.com', '', '', '',
                '', '', '', '', 
                '', '', 
                '', '', '', '', 
                '', '', '', '',
                '', '', '', ''
            ]
        ];

        $callback = function() use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        $headersResponse = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=sample_clients_import.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        return response()->stream($callback, 200, $headersResponse);
    }

    // HELPER
    private function authorizeClients()
    {
        abort_unless(auth()->user()->hasPermission('manage_clients'), 403);
    }
}
