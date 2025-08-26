<?php

namespace App\Http\Controllers;

use App\Models\Send;
use App\Models\Share;
use App\Services\ShareService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShareController extends Controller
{
    public function __construct(private ShareService $shareService){}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // dd($this->shareService->getAllShares());
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
    public function store(Request $request, $id)
    {
        return response()->json($this->shareService->saveShare($request->all(), $id));
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
    public function send(Request $request, $id)
    {
        try {
            Send::create([
                'url' => $request->url,
                'user_id' => auth()->id(),
                'type_id' => $id,
                'users' => implode(',', $request->users)
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Xatolik yuz berdi'], 500);
        }
        return response()->json(['success'=> true],200);
    }
}
