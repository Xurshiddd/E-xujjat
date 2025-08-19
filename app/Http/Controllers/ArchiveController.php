<?php

namespace App\Http\Controllers;

use App\Http\Requests\Archive\StoreRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ArchiveService;

class ArchiveController extends Controller
{
    public function __construct(protected ArchiveService $archiveService)
    {
    }
    public function index()
    {
        $archives = $this->archiveService->getAllArchives();
        return Inertia::render('Archives/Index', ['archives' => $archives]);
    }

    public function show($id)
    {
        return Inertia::render('Archives/Show', ['archive' => $id]);
    }

    public function create()
    {
        return Inertia::render('Archives/Create', ['categories' => Category::all(), 'folders' => auth()->user()->folders]);
    }

    public function store(StoreRequest $request)
    {
        try {
            $archives = $this->archiveService->saveArchive($request);
            return response()->json([
                'success' => 'Archive created successfully',
                'data' => $archives
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create archive: ' . $e->getMessage()
            ], 500);
        }
    }

}
