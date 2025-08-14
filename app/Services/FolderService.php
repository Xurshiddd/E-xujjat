<?php
namespace App\Services;

use App\Repositories\FolderRepository;
use Illuminate\Support\Facades\Log;

class FolderService {
    public function __construct(private FolderRepository $folderRepository) {}
    public function getAll(){
        return $this->folderRepository->allFolder();
    }
    public function saveFolder($data){
        try {
            $response = $this->folderRepository->createFolder($data);
            return $response;
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return false;
        }
    }
}