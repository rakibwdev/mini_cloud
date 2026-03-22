<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use App\Models\UserFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    const STORAGE_LIMIT = 524288000; // 500 MB in bytes

    /**
     * Upload a file for the authenticated user.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $uploadedFile = $request->file('file');
        $size = $uploadedFile->getSize();
        $hash = hash_file('sha256', $uploadedFile->path());
        $name = $uploadedFile->getClientOriginalName();

        return DB::transaction(function () use ($size, $hash, $name, $uploadedFile) {
            /** @var User $user */
            $user = Auth::user();
            $user = User::lockForUpdate()->find($user->id);

            // Check storage limit
            if ($user->used_storage + $size > self::STORAGE_LIMIT) {
                return response()->json(['error' => 'Storage limit exceeded'], 422);
            }

            // Check for duplicate filename for this user
            if (UserFile::where('user_id', $user->id)->where('name', $name)->exists()) {
                return response()->json(['error' => 'File with this name already exists'], 422);
            }

            // Deduplication: Check if physical file already exists
            $file = File::where('hash', $hash)->first();

            if (!$file) {
                $uploadedFile->storeAs('uploads', $hash, 'local');
                
                $file = File::create([
                    'hash' => $hash,
                    'size' => $size,
                ]);
            }

            // Create user file record
            $userFile = UserFile::create([
                'user_id' => $user->id,
                'file_id' => $file->id,
                'name' => $name,
            ]);

            // Update user storage usage
            $user->increment('used_storage', $size);

            return response()->json([
                'message' => 'File uploaded successfully',
                'file' => [
                    'id' => $userFile->id,
                    'name' => $userFile->name,
                    'size' => $file->size,
                    'upload_time' => $userFile->created_at,
                ]
            ], 201);
        });
    }

    /**
     * Delete a file for the authenticated user.
     */
    public function delete($file_id)
    {
        return DB::transaction(function () use ($file_id) {
            /** @var User $user */
            $user = Auth::user();
            $user = User::lockForUpdate()->find($user->id);
            
            $userFile = UserFile::where('user_id', $user->id)
                ->where('id', $file_id)
                ->with('file')
                ->first();

            if (!$userFile) {
                return response()->json(['error' => 'File not found'], 404);
            }

            $size = $userFile->file->size;

            // Delete user file record
            $userFile->delete();

            // Update user storage usage
            $user->decrement('used_storage', $size);

            return response()->json(['message' => 'File deleted successfully']);
        });
    }

    /**
     * Get storage summary for the authenticated user.
     */
    public function summary()
    {
        /** @var User $user */
        $user = Auth::user();
        $fileCount = UserFile::where('user_id', $user->id)->count();

        return response()->json([
            'total_storage_used' => $user->used_storage,
            'remaining_storage' => self::STORAGE_LIMIT - $user->used_storage,
            'total_active_files' => $fileCount,
        ]);
    }

    /**
     * List all files for the authenticated user.
     */
    public function list()
    {
        /** @var User $user */
        $user = Auth::user();
        
        $files = UserFile::where('user_id', $user->id)
            ->with('file')
            ->get()
            ->map(function ($userFile) {
                return [
                    'id' => $userFile->id,
                    'name' => $userFile->name,
                    'size' => $userFile->file->size,
                    'upload_time' => $userFile->created_at,
                ];
            });

        return response()->json($files);
    }
}
