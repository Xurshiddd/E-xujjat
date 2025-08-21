<?php

namespace App\Http\Controllers;

use App\Http\Requests\Folder\Storerequest;
use App\Services\FolderService;
use Inertia\Inertia;

class FolderController extends Controller
{
    public function __construct(private FolderService $service){}
    public function index()
    {
        // dd($this->service->getAll()[4]->archives->sum('file.size')); // Example to check the archives of the 5th folder
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
    public function show($id)
    {
        return Inertia::render("Folders/Show", [
            "folder" => $this->service->getById($id),
        ]);
    }
    public function update(Storerequest $request, $id)
    {
        $res = $this->service->update($id, $request->all());
        if ($res !== false) {
            return response()->json([
                "data" => $res,
                'message' => 'Folder updated successfully',
            ]);
        } else {
            return response()->json([
                'message' => 'Failed to update folder',
            ], 500);
        }
    }
    public function destroy($id)
    {
        $status = $this->service->delete($id);
        if ($status == true) {
        return response()->json([
            "message"=> "Folder succes deleted",
        ]);
    } else {
        return response()->json([
            "message"=> "failed delete folder",
        ]);
    }
}
}
