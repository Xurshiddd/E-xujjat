<?php
namespace App\Services;

use App\Repositories\FolderRepository;
use Illuminate\Support\Facades\Log;

class FolderService {
    public function __construct(private FolderRepository $folderRepository) {}
    public function getAll(){
        $data = $this->folderRepository->allFolder();
        foreach ($data as $key => $value) {
            $value['size'] = $value->archives->sum('file.size'); // Example to sum the sizes of files in each archive
        }
        return collect($data);
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