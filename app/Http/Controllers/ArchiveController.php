<?php

namespace App\Http\Controllers;

use App\Http\Requests\Archive\StoreRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\ArchiveService;

class ArchiveController extends Controller
{
    public function __construct(protected ArchiveService $archiveService)
    {}
    public function index()
    {
        $archive = $this->archiveService->getAllArchives();
        return Inertia::render('Archives/Index', ['archives' => $archive]);
    }

    public function show($id)
    {
        return Inertia::render('Archives/Show', ['archive' => $id]);
    }

    public function create()
    {
        return Inertia::render('Archives/Create');
    }

    public function store(StoreRequest $request)
    {
        $this->archiveService->saveArchive($request->all());
        return redirect()->route('archives.index')->with('success', 'Archive created successfully');
    }
}
