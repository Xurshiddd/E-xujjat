<?php
namespace App\Services;

use App\Repositories\FolderRepository;

class FolderService {
    public function __construct(private FolderRepository $folderRepository) {}
    public function getAll(){
        return $this->folderRepository->allFolder();
    }
}