<?php

namespace App\Http\Controllers;

use App\Http\Requests\Share\ShareStore;
use App\Models\Share;
use App\Services\ShareService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;


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
    public function destroy($id)
    {
        $share = Share::findOrFail( $id );
        if ($share->user_id != Auth::user()->id) {
            return response()->json([
                'error'=> 'Siz bu malumotni o\'chira olmaysiz'
                ]);
        }
        $share->delete();
        return response()->json(['status' => 'ok']);
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
        return Inertia::render('Shares/Resived', [
            'shares' => $this->shareService->getResived(),
        ]);
    }
    public function downloadByToken($token, Request $request)
    {
        return $this->shareService->downloadArchive($token, $request);
    }
}
