<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Document;
use App\Models\Folder;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    // ==========================================
    // DOCUMENT MANAGEMENT
    // ==========================================

    public function index(Request $request)
    {
        $this->authorizeFiles();

        $search = $request->search ?? '';
        $sort = $request->get('sort', 'latest');

        $users = User::where('role', 'client')
            ->select(['id', 'name', 'phone', 'is_active', 'created_at'])
            ->when($search, fn($q) =>
                $q->where('name', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%"));

        if ($sort === 'name') {
            $users->orderBy('name', 'asc');
        } else {
            $users->latest();
        }

        $users = $users->paginate(20);

        return view('admin.manage-documents', compact('users', 'search', 'sort'));
    }

    public function viewClientDocuments($id, Request $request)
    {
        $this->authorizeFiles();

        $user = User::findOrFail($id);

        // Security check (IDOR Protection)
        if ($user->role !== 'client') {
            abort(403, 'Unauthorized access to this account.');
        }

        if (Auth::user()->role === 'client' && Auth::id() != $id) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Security Violation',
                'description' => 'Attempted IDOR access to client ID: ' . $id,
                'ip_address' => $request->ip()
            ]);
            abort(403, 'Security Breach Attempt Logged.');
        }

        $currentFolderId = $request->get('folder', null);
        $currentFolder = null;
        $breadcrumbs = [];
        $folders = collect([]);

        if ($currentFolderId) {
            $currentFolder = Folder::find($currentFolderId);

            // Build Breadcrumbs
            if ($currentFolder) {
                $temp = $currentFolder;
                while ($temp) {
                    array_unshift($breadcrumbs, $temp);
                    $temp = $temp->parent;
                }
            }
        }

        // Fetch Folders
        $folders = Folder::where('user_id', $id)
            ->where('parent_id', $currentFolderId)
            ->orderBy('name')
            ->get();

        // Fetch Documents
        $documents = Document::where('user_id', $id)
            ->where('folder_id', $currentFolderId)
            ->latest()
            ->paginate(20);

        return view('admin.client-files', compact('user', 'documents', 'folders', 'currentFolder', 'breadcrumbs'));
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id'
        ]);

        // Access Control
        if (!Auth::user()->hasPermission('upload_files'))
            abort(403);

        $client = User::findOrFail($request->user_id);
        if ($client->role !== 'client')
            abort(403, 'Invalid client.');

        $parent = $request->parent_id ? Folder::find($request->parent_id) : null;

        // Calculate Path
        $path = $parent ? $parent->path . '/' . $request->name : $request->name;

        // Get or Generate unique storage name for client
        if (!$client->storage_name) {
            $client->update(['storage_name' => $this->getUniqueClientStorageName($client->name)]);
        }
        
        $wasabiPath = 'clients/' . $client->storage_name . '/' . $path;

        Folder::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'path' => $path
        ]);

        // Create folder in Wasabi (S3 doesn't have folders, so we create a placeholder)
        $disk = config('filesystems.default');
        Storage::disk($disk)->put($wasabiPath . '/.folder', '');

        return back()->with('success', 'Folder created successfully!');
    }

    public function renameFolder(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('upload_files'))
            abort(403);

        $request->validate(['name' => 'required|string|max:255']);

        $folder = Folder::findOrFail($id);

        // Update folder name and path
        $oldPath = $folder->path;
        $parent = $folder->parent;
        $newPath = $parent ? $parent->path . '/' . $request->name : $request->name;

        $folder->update([
            'name' => $request->name,
            'path' => $newPath
        ]);

        // Update all children paths recursively
        $this->updateChildrenPaths($folder);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Rename Folder',
            'description' => 'Renamed folder from ' . $folder->name . ' to ' . $request->name,
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', 'Folder renamed successfully!');
    }

    private function updateChildrenPaths($folder)
    {
        $children = Folder::where('parent_id', $folder->id)->get();
        foreach ($children as $child) {
            $child->update(['path' => $folder->path . '/' . $child->name]);
            $this->updateChildrenPaths($child);
        }
    }

    public function renameFile(Request $request, $id)
    {
        if (!Auth::user()->hasPermission('upload_files'))
            abort(403);

        $request->validate(['name' => 'required|string|max:255']);

        $doc = Document::findOrFail($id);

        // Security check
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'staff' && $doc->user_id !== Auth::id()) {
            abort(403, 'Unauthorized renaming attempt.');
        }
        $oldPath = $doc->file_path;
        $extension = pathinfo($oldPath, PATHINFO_EXTENSION);

        // Check if new name has extension, if not, append it
        $newName = $request->name;
        if (pathinfo($newName, PATHINFO_EXTENSION) !== $extension) {
            $newName .= '.' . $extension;
        }

        // Generate New Path
        // Keep in same directory structure (documents/category/...) or (documents/category/timestamp_uniqid_name)
        // Usually we just want to change the display name, but user asked for "same file structure in Wasabi".
        // Current storeAs uses: documents/{category}/{timestamp}_{uniqid}_{originalName}

        // We will preserve the directory prefix but change the filename part.
        $directory = dirname($oldPath);
        // To keep unique reference safe, we might just want to rename the visible part, 
        // but typically S3 renaming implies copying to a new key.
        // Let's assume we want the stored file to actually have the new name relative to its directory.
        $newPath = $directory . '/' . time() . '_' . $newName;

        $disk = config('filesystems.default');

        try {
            if (Storage::disk($disk)->exists($oldPath)) {
                // S3 Copy & Delete = Rename
                Storage::disk($disk)->copy($oldPath, $newPath);
                Storage::disk($disk)->delete($oldPath);

                $doc->update([
                    'filename' => $newName,
                    'file_path' => $newPath
                ]);

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Rename',
                    'description' => 'Renamed ' . $doc->filename . ' to ' . $newName,
                    'ip_address' => $request->ip()
                ]);

                return back()->with('success', 'File renamed successfully!');
            } else {
                return back()->with('error', 'Original file not found in storage.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to rename file: ' . $e->getMessage());
        }
    }

    public function getFolders($userId)
    {
        // SECURITY: Only users with view_files permission can access folder structure
        if (!auth()->user()->hasPermission('view_files')) {
            abort(403, 'Unauthorized access.');
        }

        $folders = Folder::where('user_id', $userId)->orderBy('name')->get(['id', 'name', 'parent_id']);
        return response()->json($folders);
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('upload_files')) {
            abort(403, 'Access Denied.');
        }

        $users = User::where('role', 'client')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.upload-file', compact('users'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('upload_files')) {
            abort(403, 'Access Denied.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'folder_id' => 'nullable', // Allow Root Uploads
            'files' => 'required',
            'files.*' => 'file|max:51200|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip',
        ]);

        if ($request->hasFile('files')) {
            $user = User::findOrFail($request->user_id);
            $folder = $request->folder_id ? Folder::find($request->folder_id) : null;
            
            // Get or Generate unique storage name for client
            if (!$user->storage_name) {
                $user->update(['storage_name' => $this->getUniqueClientStorageName($user->name)]);
            }
            
            // Build Wasabi path: clients/{storage_name}/{folder_path}/
            $folderPath = $folder ? $folder->path : 'General';
            $basePath = 'clients/' . $user->storage_name . '/' . $folderPath;

            foreach ($request->file('files') as $file) {
                
                $originalName = $file->getClientOriginalName();
                $disk = config('filesystems.default');
                $finalName = $this->getUniqueFilename($disk, $basePath, $originalName);
                
                // Store in Wasabi/Local
                $path = $file->storeAs($basePath, $finalName, $disk);

                Document::create([
                    'user_id' => $request->user_id,
                    'filename' => $originalName,
                    'file_path' => $path,
                    'category' => $folder ? $folder->name : 'General Repository', 
                    'folder_id' => $folder ? $folder->id : null,
                ]);

                // LOG: File Upload
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Upload',
                    'description' => 'Uploaded file: ' . $originalName . ' to ' . ($folder ? $folder->name : 'Root'),
                    'ip_address' => $request->ip()
                ]);
            }
            return back()->with('success', 'Files Uploaded Successfully to ' . ($folder ? $folder->name : 'Root Repository') . '!');
        }
        return back()->with('error', 'Please select files.');
    }

    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin')
            abort(403);

        $doc = Document::findOrFail($id);
        $disk = config('filesystems.default'); // local or wasabi

        if (Storage::disk($disk)->exists($doc->file_path)) {
            Storage::disk($disk)->delete($doc->file_path);
        }
        $doc->delete();

        return back()->with('success', 'Document Deleted!');
    }

    public function trackAndDownload($id)
    {
        return $this->handleFileAccess($id, 'attachment');
    }

    public function viewInline($id)
    {
        return $this->handleFileAccess($id, 'inline');
    }

    private function handleFileAccess($id, $disposition)
    {
        $doc = Document::findOrFail($id);
        $user = Auth::user();

        // Security Check (IDOR Fix)
        if ($user->role !== 'admin' && $user->role !== 'staff' && $doc->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this document.');
        }

        $disk = config('filesystems.default');

        // Verify file exists
        if (!Storage::disk($disk)->exists($doc->file_path)) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'Access Failed',
                'description' => 'File not found: ' . $doc->filename,
                'ip_address' => request()->ip()
            ]);
            return back()->with('error', 'The file could not be found.');
        }

        // Log Activity
        $action = $disposition === 'inline' ? 'Viewed Document' : 'Downloaded Document';
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'description' => $doc->filename,
            'ip_address' => request()->ip()
        ]);

        if ($disk !== 'local') {
            try {
                $options = [];
                if ($disposition === 'attachment') {
                    $options['ResponseContentDisposition'] = 'attachment; filename="' . $doc->filename . '"';
                } else {
                    $options['ResponseContentDisposition'] = 'inline; filename="' . $doc->filename . '"';
                }

                /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
                $storage = Storage::disk($disk);
                $url = $storage->temporaryUrl($doc->file_path, now()->addMinutes(15), $options);
                return redirect($url);
            } catch (\Exception $e) {
                /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
                $storage = Storage::disk($disk);
                return $disposition === 'attachment'
                    ? $storage->download($doc->file_path, $doc->filename)
                    : response($storage->get($doc->file_path), 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="' . $doc->filename . '"'
                    ]);
            }
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk($disk);
        return $disposition === 'attachment'
            ? $storage->download($doc->file_path, $doc->filename)
            : response()->file($storage->path($doc->file_path), [
                'Content-Disposition' => 'inline; filename="' . $doc->filename . '"'
            ]);
    }

    // HELPER
    private function authorizeFiles()
    {
        abort_unless(auth()->user()->hasPermission('view_files'), 403);
    }

    /**
     * Helper to handle filename conflicts by appending (1), (2), etc.
     */
    private function getUniqueFilename($disk, $directory, $originalName)
    {
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $extension = $extension ? '.' . $extension : '';
        
        $path = $directory . '/' . $originalName;
        $counter = 1;
        
        while (Storage::disk($disk)->exists($path)) {
            $path = $directory . '/' . $filename . ' (' . $counter . ')' . $extension;
            $counter++;
        }
        
        return basename($path);
    }

    /**
     * Helper to generate a unique storage folder name for a client.
     */
    private function getUniqueClientStorageName($name)
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_\- ]/', '', $name);
        $storageName = $safeName;
        $counter = 1;

        while (User::where('storage_name', $storageName)->exists()) {
            $storageName = $safeName . ' (' . $counter . ')';
            $counter++;
        }

        return $storageName;
    }
}
