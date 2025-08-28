<?php

namespace App\Services;

use App\Models\Archive;
use App\Models\Share;
use App\Models\Folder;
use App\Models\User;
use DB;
use Hash;
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
        try {
            DB::beginTransaction();
            $share = $this->share->create([
                "user_id" => auth()->id(),
                "shareable_id" => $id,
                "shareable_type" => $data['type'] == 'archive' ? Archive::class : Folder::class,
                "token" => $this->extractLastSegment($data['url']),
                "url" => $data['url'],
                "expires_at" => $data['expires_at'],
                "password" => $data['password'] ? Hash::make($data['password']) : null,
            ]);
            foreach ($data['users'] as $userId) {
                DB::table('shareable')->insert([
                    'sender_id'=> auth()->id(),
                    'share_id' => $share->id,
                    'resiver_id' => $userId,
                ]);
            }
            // shareable
            DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "success"
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            return response()->json([
                "status" => "error",
                "message" => $th->getMessage()
            ]);
        }
    }

    public function getAllShares()
    {
       $shares = DB::table('shares as s')
            ->select(
                's.id',
                's.url',
                's.expires_at',
                's.shareable_type',
                's.shareable_id',
                DB::raw('GROUP_CONCAT(u.name, ",") as receivers')
            )
            ->join('shareable as sh', 's.id', '=', 'sh.share_id')
            ->join('users as u', 'sh.resiver_id', '=', 'u.id')
            ->where('s.user_id', auth()->id())
            ->groupBy('s.id', 's.url', 's.expires_at', 's.shareable_type', 's.shareable_id')
            ->orderByDesc('s.id')
            ->get();

        // shareable_type orqali nomini olish (Folder yoki Archive jadvalidan)
        $shares->transform(function ($item) {
            if ($item->shareable_type === \App\Models\Folder::class) {
                $item->shareable_name = DB::table('folders')->where('id', $item->shareable_id)->value('name');
            } else {
                $item->shareable_name = DB::table('archives')->where('id', $item->shareable_id)->value('title');
            }
            $item->receivers = explode(',', $item->receivers);
            return $item;
        });

        return $shares;
    }
    public function generateUrl(array $data): string
    {
        // 1) Normalize: faqat kerakli kalitlarni oling va tartibda saqlang
        $payload = [
            'expires_at' => $this->normalizeExpiresAt($data['expires_at'] ?? null),
            'password' => $data['password'] ? Hash::make($data['password']) : null,
            'type' => $data['type'] ?? null,
            'id' => $data['id'] ?? null,
        ];

        // 2) Canonical (deterministic) string hosil qilish: sort va json_encode
        ksort($payload); // kalitlarni alfabetik tartibda ta'minlaydi
        // json_encode -> unicode/utf8 bilan barqaror bo'lsin
        $canonical = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // 3) Xavfsiz hash: app key bilan HMAC-SHA256
        $key = config('app.key') ?: env('APP_KEY'); // odatda app.key mavjud bo'ladi
        // app.key Laravel formatida "base64:..." bo'lishi mumkin — shu holatni tozalaymiz:
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        $fullHash = hash_hmac('sha256', $canonical, $key);

        // 4) Agar qisqaroq sluggable hash kerak bo'lsa, substr bilan qisqartirish mumkin.
        // Masalan 16 ta hex belgidan iborat qismini olib URLga qo'yamiz:
        $shortHash = substr($fullHash, 0, 20); // 20 hex chars -> 80 bit taxminan

        // 5) Agar iloji bo'lsa collision oldini olish uchun siz DBda uniqueness tekshirishingiz va
        // kerak bo'lsa yana bir qatlam (timestamp yoki random salt) qo'shishingiz mumkin.
        return $shortHash;
    }
    protected function normalizeExpiresAt($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Agar DateTime yoki timestamp bo'lsa uni ISO 8601 tarzida formatlang
        try {
            $dt = new \DateTime($value);
            // UTC yoki local — sizga qulay bo'lgan formatni ishlating
            return $dt->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // Noto'g'ri format bo'lsa, asl qiymatini qaytaring yoki null qilish mumkin
            return (string) $value;
        }
    }
    function extractLastSegment(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path)
            return null;
        $segment = basename($path);
        return $segment ?: null;
    }
}
