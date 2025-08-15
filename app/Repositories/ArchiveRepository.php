<?php

namespace App\Repositories;

use App\Models\Archive;

class ArchiveRepository
{
    /**
     * Create a new class instance.
     */
    public function __construct(private Archive $archive)
    {
        //
    }
    public function AllArchives(): array
    {
        return $this->archive->with(['folder', 'user', 'category'])->get()->toArray();
    }
    public function CreateArchive(array $data): Archive
    {
        return $this->archive->create($data);
    }
}
