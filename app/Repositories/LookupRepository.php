<?php

namespace App\Repositories;

use App\Models\Department;
use App\Models\Level;

class LookupRepository
{
    /**
     * Get all levels ordered by rank.
     */
    public function getLevels()
    {
        return Level::orderBy('rank')->get();
    }

    /**
     * Get all departments with their job titles.
     */
    public function getDepartmentsWithJobTitles()
    {
        return Department::with(['jobTitles' => function ($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get();
    }
}
