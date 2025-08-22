<?php

namespace App\Services;

use App\Models\Archive;
use App\Models\Share;
use App\Models\Folder;
use Log;

class ShareService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private Share $share)
    {
        //
    }
    public function saveShare($data, $id)
    {
        $validated = validator($data, [
            "type" => "required|string|in:folder,archive",
            "expires_at" => "nullable|date|after:now",
            "password" => "nullable|string|min:6",
        ])->validate();
        $token = \Str::random(32);
         $this->share->create([
            'shareable_type' => $data['type'] == 'folder' ? Folder::class : Archive::class,
            'user_id' => auth()->id(),
            'shareable_id' => $id,
            'expires_at' => $data['expires_at'] ?? null,
            'password' => isset($data['password']) ? bcrypt($data['password']) : null,
            'token' => $token,
        ]);
        return [
            'url' => url('/s/shared' . $token),
            'token' => $token,
        ];
    }
}
