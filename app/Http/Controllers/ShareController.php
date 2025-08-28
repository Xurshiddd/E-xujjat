<?php

namespace App\Http\Controllers;

use App\Http\Requests\Share\ShareStore;
use App\Models\Share;
use App\Models\Folder;
use App\Models\Archive;
use ZipArchive;
use App\Services\ShareService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ShareController extends Controller
{
    public function __construct(private ShareService $shareService)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Shares/Send', [
            'shares' => $this->shareService->getAllShares()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ShareStore $request, $id)
    {
        return $this->shareService->saveShare($request->all(), $id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Share $share)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Share $share)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Share $share)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Share $share)
    {
        //
    }
    public function generateurl(Request $request, $id)
    {
        $data = [
            'expires_at' => $request->input('expires_at'), // yyyy-mm-dd H:i:s yoki null
            'password' => $request->input('password'),   // null yoki string
            'type' => $request->input('type'),       // kerakli type
            'id' => $id,
        ];

        $hash = $this->shareService->generateUrl($data);

        $url = url("/s/document/{$hash}");

        return response()->json(['url' => $url]);
    }

    public function received()
    {
        $shares = DB::table('shareable as sh')
            ->select(
                's.id',
                's.url',
                's.expires_at',
                's.shareable_type',
                's.shareable_id',
                's.password',
                's.token',
                'u.name as sender_name'
            )
            ->join('shares as s', 'sh.share_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->where('sh.resiver_id', auth()->id())
            ->orderByDesc('s.id')
            ->get();

        // Type va name ni qo‘shish
        $shares->transform(function ($item) {
            if ($item->shareable_type === Folder::class) {
                $item->shareable_name = DB::table('folders')->where('id', $item->shareable_id)->value('name');
                $item->type = 'Folder';
            } else {
                $item->shareable_name = DB::table('archives')->where('id', $item->shareable_id)->value('title');
                $item->type = 'Archive';
            }
            $item->has_password = $item->password ? true : false;
            unset($item->password);
            return $item;
        });

        return Inertia::render('Shares/Resived', [
            'shares' => $shares,
        ]);
    }
    public function downloadByToken($token, Request $request)
    {
        $share = Share::where('token', $token)->first();

        if ($share && Hash::check($request->password, $share->password)) {
            if ($share->expires_at && $share->expires_at->isPast()) {
                return response()->json(['message' => 'Link expired'], 410);
            }
            // Agar folder bo'lsa
            if ($share->shareable_type === Folder::class) {
                $folder = Folder::findOrFail($share->shareable_id);
                $archives = $folder->archives;

                if ($archives->isEmpty()) {
                    return response()->json(['error' => 'Folder bo‘sh'], 404);
                }

                // vaqtinchalik zip nomi
                $zipFileName = 'folder_' . $folder->id . '_' . time() . '.zip';
                $zipPath = storage_path("app/public/{$zipFileName}");

                $zip = new ZipArchive;
                if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                    foreach ($archives as $archive) {
                        if ($archive->file) {
                            $filePath = storage_path('app/public/' . $archive->file->path);
                            if (file_exists($filePath)) {
                                // zip ichida fayl nomi archive name bo‘lsin
                                $zip->addFile($filePath, $archive->name . '.' . pathinfo($filePath, PATHINFO_EXTENSION));
                            }
                        }
                    }
                    $zip->close();
                } else {
                    return response()->json(['error' => 'Zip yaratib bo‘lmadi'], 500);
                }

                // Zip faylni yuklab berish va keyin o‘chirish
                return response()->download($zipPath)->deleteFileAfterSend(true);
            }

            // Agar archive bo‘lsa
            if ($share->shareable_type === Archive::class) {
                $archive = Archive::findOrFail($share->shareable_id);
                if ($archive->file) {
                    $filePath = storage_path('app/public/' . $archive->file->path);
                    return response()->download($filePath, $archive->name . '.' . pathinfo($filePath, PATHINFO_EXTENSION));
                }
                return response()->json(['error' => 'Fayl topilmadi'], 404);
            }
        }

        return response()->json(['error' => 'Token yoki parol noto‘g‘ri'], 403);
    }
}
