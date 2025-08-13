<?php

namespace App\Repositories;

use App\Models\Folder;
use Illuminate\Support\Facades\Auth;

class FolderRepository
{
    /**
     * Create a new class instance.
     */
    private $folder;
    public function __construct(Folder $folder)
    {
        $this->folder = $folder;
    }
    public function allFolder(){
        return $this->folder->where('id', Auth::id())->paginate(10);
    }
}
