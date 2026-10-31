<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends BaseModel
{
    protected $fillable = [
        'name',
    ];

    public function governorates(): HasMany
    {
        return $this->hasMany(Governorate::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
