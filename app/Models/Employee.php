<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable
{
    use HasApiTokens, HasUuids;

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'date_of_birth'=> 'date',
        'is_verified'=> 'boolean',
    ];


    // ---------- Accessors ----------

    //full name accessor: combines first and last name into one string
    protected function fullName() : Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    //avatar_link accessor: returns the full URL to the avatar image if it exists, otherwise null
    protected function avatarUrl() : Attribute
    {
        return Attribute::make(
            get: fn () => $this->avatar_uri ? Storage::disk('public')->url($this->avatar_uri) : null,
        );
    }

    // position_label accessor: combines level and job title into one string
    protected function positionLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->level?->name} {$this->jobTitle?->name}") ?: null,
        );
    }

    // address accessor: one formatted line from the structured address
    protected function address(): Attribute{

        return Attribute::make(
            get: fn () => collect([
                $this->full_address,
                $this->city?->name,
                $this->governorate?->name,
                $this->country?->name,
            ])->filter()->implode(', ')?: null,
        );
    }

}
