<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ArchiveController extends Controller
{
    public function index()
    {
        return Inertia::render('Archives/Index');
    }

    public function show($id)
    {
        return Inertia::render('Archives/Show', ['archive' => $id]);
    }

    public function create()
    {
        return Inertia::render('Archives/Create');
    }

    public function store(Request $request)
    {
        // Logic to store the archive
        return redirect()->route('archives.index')->with('success', 'Archive created successfully');
    }
}
