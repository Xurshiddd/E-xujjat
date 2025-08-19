<?php

namespace App\Services;
use App\Events\AttachmentEvent;
use App\Repositories\ArchiveRepository;
use App\Models\Archive;
use App\Services\AttachmentService;
use Illuminate\Http\Request;

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
    public function saveArchive(Request $request): array
    {
        $archives = [];
        $data = $request->all();
        $data['user_id'] = auth()->id();

        $c_data = collect($data)->except(['files'])->toArray();

        foreach ($request->file('files') as $file) {
            $c_data['name'] = $file->getClientOriginalName();
            $archive = $this->archiveRepository->createArchive($c_data);
            $archives[] = $archive;

            // Har bir faylni AttachmentEvent ga jo‘natamiz
            event(new AttachmentEvent([$file], $archive->file(), 'archives'));
        }

        return $archives;
    }

}
