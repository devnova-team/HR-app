<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends BaseModel
{
    protected $fillable = [
        'name',
        'rank',
    ];

    protected $casts = [
        'rank' => 'integer',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
