<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WebsiteTeamMember;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminTeamController extends Controller
{
    /**
     * Helper to enforce permissions.
     * Admins are always authorized, Staff must have 'manage_team' permission.
     */
    private function authorizeTeam()
    {
        abort_unless(auth()->user()->hasPermission('manage_team'), 403);
    }

    /**
     * Determine the safe folder path for team member image storage (Hostinger safe setup).
     */
    protected function getTeamDir()
    {
        $dirPublic = public_path('assets/img/team');
        $hostingerDir = base_path('../assets/img/team');
        
        $dir = File::exists($hostingerDir) ? $hostingerDir : $dirPublic;
        
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        
        return $dir;
    }

    /**
     * Display a listing of the website team members.
     */
    public function index()
    {
        $this->authorizeTeam();

        // Dynamically run migrations programmatically if table is missing (hands-free server setup)
        if (!\Illuminate\Support\Facades\Schema::hasTable('website_team_members')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } catch (\Exception $e) {
                Log::error('Auto migration failed in Team Manager: ' . $e->getMessage());
            }
        }

        // Auto-seed existing fallback team members if table is empty
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('website_team_members')) {
                if (WebsiteTeamMember::count() === 0) {
                    $this->seedFallbackMembers();
                }
            }
        } catch (\Exception $e) {
            Log::error('Auto-seeding website team members failed: ' . $e->getMessage());
        }

        $members = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('website_team_members')) {
                $members = WebsiteTeamMember::orderBy('display_order', 'asc')->get();
            } else {
                session()->flash('warning', 'The database table "website_team_members" does not exist yet. Please configure your database connection.');
            }
        } catch (\Exception $e) {
            Log::error('Website Team Members Retrieve Error: ' . $e->getMessage());
            session()->flash('error', 'Database query failed: ' . $e->getMessage());
        }

        return view('admin.team.index', compact('members'));
    }

    /**
     * Show the form for creating a new website team member.
     */
    public function create()
    {
        $this->authorizeTeam();
        return view('admin.team.create');
    }

    /**
     * Store a newly created website team member in the database.
     */
    public function store(Request $request)
    {
        $this->authorizeTeam();

        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'qualification' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'image' => 'required|string', // Base64 cropped WebP (Mandatory!)
            'tags' => 'nullable|string', // Comma separated tags (e.g. "Taxation, Audit")
        ]);

        try {
            $imagePath = null;

            if ($request->filled('image')) {
                $base64Image = $request->input('image');
                
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                    $rawImage = substr($base64Image, strpos($base64Image, ',') + 1);
                    $rawImage = base64_decode($rawImage);

                    if ($rawImage !== false) {
                        $filename = 'team_' . Str::random(10) . '_' . time() . '.webp';
                        $savePath = $this->getTeamDir() . '/' . $filename;
                        File::put($savePath, $rawImage);
                        $imagePath = 'assets/img/team/' . $filename;
                    }
                }
            }

            // Process tags
            $tagsArray = [];
            if ($request->filled('tags')) {
                $tagsArray = array_map('trim', explode(',', $request->tags));
                $tagsArray = array_filter($tagsArray); // remove empty tags
            }

            // Determine next display order
            $nextOrder = WebsiteTeamMember::max('display_order') + 1;

            // Automatically determine category based on role
            $roleLower = strtolower($request->role);
            $category = (str_contains($roleLower, 'partner') || str_contains($roleLower, 'founder')) ? 'partner' : 'associate';

            $member = WebsiteTeamMember::create([
                'name' => $request->name,
                'role' => $request->role,
                'category' => $category,
                'qualification' => $request->qualification,
                'bio' => $request->bio,
                'image_path' => $imagePath,
                'tags' => $tagsArray,
                'display_order' => $nextOrder,
            ]);

            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Website Team Member Created',
                'description' => "Added website team member: {$member->name} ({$member->role})",
                'ip_address' => $request->ip()
            ]);

            return redirect()->route('admin.team.index')
                ->with('success', 'Team Member added successfully!');

        } catch (\Exception $e) {
            Log::error('Website Team Member Store Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to add team member: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified website team member.
     */
    public function edit($id)
    {
        $this->authorizeTeam();
        $member = WebsiteTeamMember::findOrFail($id);
        
        $tagsString = $member->tags ? implode(', ', $member->tags) : '';

        return view('admin.team.edit', compact('member', 'tagsString'));
    }

    /**
     * Update the specified website team member in the database.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeTeam();
        $member = WebsiteTeamMember::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'qualification' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'image' => 'nullable|string', // Base64 cropped WebP (or null if unchanged)
            'tags' => 'nullable|string',
        ]);

        try {
            $imagePath = $member->image_path;

            if ($request->filled('image')) {
                $base64Image = $request->input('image');
                
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                    $rawImage = substr($base64Image, strpos($base64Image, ',') + 1);
                    $rawImage = base64_decode($rawImage);

                    if ($rawImage !== false) {
                        // Delete previous image if exists
                        if ($member->image_path) {
                            $oldFile = public_path($member->image_path);
                            $oldHostingerFile = base_path('../' . $member->image_path);
                            if (File::exists($oldFile)) {
                                File::delete($oldFile);
                            } elseif (File::exists($oldHostingerFile)) {
                                File::delete($oldHostingerFile);
                            }
                        }

                        $filename = 'team_' . Str::random(10) . '_' . time() . '.webp';
                        $savePath = $this->getTeamDir() . '/' . $filename;
                        File::put($savePath, $rawImage);
                        $imagePath = 'assets/img/team/' . $filename;
                    }
                }
            }

            // Process tags
            $tagsArray = [];
            if ($request->filled('tags')) {
                $tagsArray = array_map('trim', explode(',', $request->tags));
                $tagsArray = array_filter($tagsArray);
            }

            // Automatically determine category based on role
            $roleLower = strtolower($request->role);
            $category = (str_contains($roleLower, 'partner') || str_contains($roleLower, 'founder')) ? 'partner' : 'associate';

            $member->update([
                'name' => $request->name,
                'role' => $request->role,
                'category' => $category,
                'qualification' => $request->qualification,
                'bio' => $request->bio,
                'image_path' => $imagePath,
                'tags' => $tagsArray,
            ]);

            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Website Team Member Updated',
                'description' => "Updated website team member: {$member->name}",
                'ip_address' => $request->ip()
            ]);

            return redirect()->route('admin.team.index')
                ->with('success', 'Team Member updated successfully!');

        } catch (\Exception $e) {
            Log::error('Website Team Member Update Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update team member: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified website team member from the database and delete their picture.
     */
    public function destroy(Request $request, $id)
    {
        $this->authorizeTeam();
        $member = WebsiteTeamMember::findOrFail($id);

        try {
            // Delete image file if exists
            if ($member->image_path) {
                $filePath = public_path($member->image_path);
                $hostingerFilePath = base_path('../' . $member->image_path);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                } elseif (File::exists($hostingerFilePath)) {
                    File::delete($hostingerFilePath);
                }
            }

            $memberName = $member->name;
            $member->delete();

            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Website Team Member Deleted',
                'description' => "Deleted website team member: {$memberName}",
                'ip_address' => $request->ip()
            ]);

            return redirect()->route('admin.team.index')
                ->with('success', 'Team Member deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Website Team Member Delete Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete team member: ' . $e->getMessage());
        }
    }

    /**
     * Reorder website team members' display order.
     */
    public function reorder(Request $request)
    {
        $this->authorizeTeam();

        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:website_team_members,id',
        ]);

        try {
            foreach ($request->order as $index => $id) {
                WebsiteTeamMember::where('id', $id)->update(['display_order' => $index]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Display order updated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Website Team Members Reorder Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update display order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Seeds the 8 fallback website team members into the database table.
     */
    private function seedFallbackMembers()
    {
        $fallbackPartners = [
            [
                'name' => 'Rakesh Sharma',
                'role' => 'Managing Director',
                'qualification' => '',
                'bio' => 'Over 25 years of experience in chemical procurement and supply chain management.',
                'image_path' => 'images/member_md.jpeg',
                'tags' => ['Leadership', 'Procurement'],
                'category' => 'partner',
                'display_order' => 1
            ],
            [
                'name' => 'Dr. Anjali Desai',
                'role' => 'Technical Lead',
                'qualification' => 'Ph.D. Analytical Chemistry',
                'bio' => 'Ensures quality control and client technical support across the catalog.',
                'image_path' => 'images/member_tech.jpeg',
                'tags' => ['Quality Control', 'Technical Support'],
                'category' => 'partner',
                'display_order' => 2
            ]
        ];

        $fallbackAssociates = [
            [
                'name' => 'Vikram Singh',
                'role' => 'Head of Logistics',
                'qualification' => '',
                'bio' => 'Specialist in hazardous materials transport and warehouse inventory management.',
                'image_path' => 'images/member_logistics.jpeg',
                'tags' => [],
                'category' => 'associate',
                'display_order' => 3
            ],
            [
                'name' => 'Priya Patel',
                'role' => 'Client Relations Manager',
                'qualification' => '',
                'bio' => 'Dedicated to understanding and serving the unique needs of our B2B partners.',
                'image_path' => 'images/member_relations.jpeg',
                'tags' => [],
                'category' => 'associate',
                'display_order' => 4
            ],
            [
                'name' => 'Arun Kumar',
                'role' => 'Procurement Specialist',
                'qualification' => '',
                'bio' => 'Expert in sourcing rare and high-purity reagents from global markets.',
                'image_path' => 'images/member_procure.jpeg',
                'tags' => [],
                'category' => 'associate',
                'display_order' => 5
            ],
            [
                'name' => 'Meena Reddy',
                'role' => 'Quality Assurance Officer',
                'qualification' => '',
                'bio' => 'Maintains our ISO 9001:2015 standards across all incoming inventory batches.',
                'image_path' => 'images/member_qa.jpeg',
                'tags' => [],
                'category' => 'associate',
                'display_order' => 6
            ]
        ];

        foreach (array_merge($fallbackPartners, $fallbackAssociates) as $member) {
            WebsiteTeamMember::create([
                'name' => $member['name'],
                'role' => $member['role'],
                'qualification' => $member['qualification'],
                'bio' => $member['bio'],
                'image_path' => $member['image_path'],
                'tags' => $member['tags'] ?? [],
                'category' => $member['category'],
                'display_order' => $member['display_order']
            ]);
        }
    }
}
