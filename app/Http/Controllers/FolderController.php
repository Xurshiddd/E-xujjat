<?php

namespace App\Http\Controllers;

use App\Http\Requests\Folder\Storerequest;
use App\Services\FolderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use SebastianBergmann\Complexity\Complexity;

class FolderController extends Controller
{
    public function __construct(private FolderService $service){}
    public function index()
    {
        return Inertia::render("Folders/Index", [
            "folders" => $this->service->getAll(),
        ]);
    }
    public function store(Storerequest $request){
        $data = $this->service->saveFolder($request->all());
        if ($data) {
            return response()->json([
                'message' => 'Folder created successfully',
                'data' => $data,
            ], 201);
        } else {
            return response()->json([
                'message' => 'Failed to create folder',
            ], 500);
        }
    }
}
