<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminUploadController extends Controller
{
    /**
     * Handle FilePond file upload.
     */
    public function upload(Request $request)
    {
        // Try 'avatar' or 'file' field
        $file = $request->file('avatar') ?? $request->file('file');

        if ($file) {
            $folder = $request->input('folder', 'uploads');
            $userId = $request->input('user_id');

            // If it's an avatar and we have a user_id, use avatars/{user_id}
            if ($request->has('avatar') || $folder === 'avatars') {
                $folder = 'avatars/' . ($userId ?? 'temp');
            }

            $path = $file->store($folder, 'public');
            
            return response()->json([
                'url' => url(Storage::url($path)),
                'path' => $path
            ]);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }

    /**
     * Handle FilePond revert (delete temporary upload).
     */
    public function revert(Request $request)
    {
        $path = $request->getContent();
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return response()->json(['message' => 'File deleted']);
        }

        return response()->json(['message' => 'File not found'], 404);
    }
}
