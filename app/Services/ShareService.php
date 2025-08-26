<?php

namespace App\Services;

use App\Models\Archive;
use App\Models\Share;
use App\Models\Folder;
use App\Models\User;
use Log;
use PhpParser\NodeVisitor\FirstFindingVisitor;

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
            'url' => url('/s/shared/' . $token),
            'token' => $token,
        ];
    }
    public function getAllShares()
    {
        $user = User::with('sends')->find(auth()->id());

        $shares = $user->sends->map(function ($send) {
            $share = $send->shareable; // Polymorphic related model
            return [
                'type' => class_basename($share->getMorphClass()),  // "Folder" or "Archive"
                'url' => $send->url,
                'users' => implode(', ', User::whereIn('id', explode(',', $send->users))->pluck('name')->toArray()),
                'expires_at' => $share->expires_at,
                'password' => $share->password ? 'Yes' : 'No',
            ];
        });


        return $shares;
    }

}
