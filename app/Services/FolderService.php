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
    public function getById($id){
        try {
            $response = $this->folderRepository->showFolder($id);
            $response['size'] = format_file_size($response->archives->sum('file.size'));// Assuming you have a helper function to format file size
            foreach ($response->archives as $archive) {
                $archive->file->size = format_file_size($archive->file->size ?? 0); // Format each file size
            }
            return $response;
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return false;
        }
    }
}