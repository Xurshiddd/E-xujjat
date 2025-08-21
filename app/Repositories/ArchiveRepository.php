<?php

namespace App\Repositories;

use App\Models\Archive;
use App\Services\AttachmentService;

class ArchiveRepository
{
    /**
     * Create a new class instance.
     */
    public function __construct(private Archive $archive, private AttachmentService $attachmentService)
    {
        //
    }
    public function AllArchives(): array
    {
        return $this->archive->with(['folder', 'user', 'category', 'file'])->where('user_id', auth()->id())->get()->toArray();
    }
    public function CreateArchive(array $data): Archive
    {
        return $this->archive->create($data);
    }
    public function DeleteArchive(int $id): bool
    {
        $archive = $this->archive->find($id);
        if ($archive) {
            if ($archive->file != null) {
                $this->attachmentService->destroy($archive->file);
            }
            $archive->delete();
        }
        return $archive ? true : false;
    }
    // public function updateArchive(int $id, array $data): bool
    // {

    // }
}
