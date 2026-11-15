<?php

namespace App\Services;

use App\Repositories\LookupRepository;

class LookupService
{
    public function __construct(
        protected LookupRepository $lookupRepository
    ) {}

    public function getPositionsLookup(): array
    {
        return [
            'levels' => $this->lookupRepository->getLevels(),
            'departments' => $this->lookupRepository->getDepartmentsWithJobTitles(),
        ];
    }
}
