<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends BaseModel
{
    protected $fillable = [
        'name',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function jobTitles(): HasMany
    {
        return $this->hasMany(JobTitle::class);
    }
}
