<?php

namespace App\Http\Controllers;

use App\Http\Requests\Archive\StoreRequest;
use App\Http\Requests\Archive\UpdateRequest;
use App\Models\Archive;
use App\Models\Category;
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
    public function destroy($id)
    {
        $arch = Archive::find($id);
        try {
            $result = $this->archiveService->deleteArchive($id);
            // dd($result);
            if ($result) {
                return response()->json(['success' => 'Archive deleted successfully'], 200);
            } else {
                return response()->json(['error' => 'Archive not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete archive: ' . $e->getMessage()], 500);
        }
    }
    public function edit($id)
    {
        $archive = Archive::with('file')->find($id);
        // dd($archive);
        if (!$archive) {
            return response()->json(['error' => 'Archive not found'], 404);
        }

        return Inertia::render('Archives/Edit', [
            'archive' => $archive,
            'categories' => Category::all(),
            'folders' => auth()->user()->folders()->get(),
        ]);
    }
    public function update(UpdateRequest $request, $id)
    {
        try {
            $result = $this->archiveService->updateArchive($id, $request);
            if ($result) {
                return response()->json(['success' => 'Archive updated successfully'], 200);
            } else {
                return response()->json(['error' => 'Archive not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update archive: ' . $e->getMessage()], 500);
        }
    }
}
