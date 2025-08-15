<?php

namespace App\Services;
use App\Events\AttachmentEvent;
use App\Repositories\ArchiveRepository;
use App\Models\Archive;
use App\Services\AttachmentService;
class ArchiveService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private ArchiveRepository $archiveRepository, protected AttachmentService $attachmentService)
    {
        //
    }
    public function getAllArchives(): array
    {
        return $this->archiveRepository->AllArchives();
    }
    public function saveArchive($data): Archive
    {
        $data['user_id'] = auth()->id();
        $c_data = $data->except(['files']);
        $archive = $this->archiveRepository->CreateArchive($c_data);
        event((new AttachmentEvent($data['files'], $archive->files(), 'archives')));
        return $archive;
    }
}
