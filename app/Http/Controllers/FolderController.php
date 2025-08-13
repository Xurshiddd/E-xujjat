<?php

namespace App\Http\Controllers;

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
}
