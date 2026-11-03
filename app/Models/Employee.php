<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Authenticatable
{
    use HasApiTokens, HasRoles ,HasUuids;

    protected $fillable = [
        'level_id',
        'department_id',
        'job_title_id',
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'date_of_birth',
        'avatar_url',
        'full_address',
        'country_id',
        'governorate_id',
        'city_id',
        'is_verified',
        'is_first_login',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'is_verified' => 'boolean',
    ];

    // ------------- Relationships ----------

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function meetings(): BelongsToMany
    {
        return $this->belongsToMany(Meeting::class, 'meeting_participants');
    }

    public function passwordResetOtps()
    {
        return $this->hasMany(PasswordResetOtp::class);
    }

    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_by_id');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(
            Task::class,
            'task_assignees',
            'employee_id',
            'task_id'
        );
    }

    // ---------- Accessors ----------

    // full name accessor: combines first and last name into one string
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    // avatar_link accessor: returns the full URL to the avatar image if it exists, otherwise null
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->avatar_url ? Storage::disk('public')->url($this->avatar_url) : null,
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
    protected function address(): Attribute
    {

        return Attribute::make(
            get: fn () => collect([
                $this->full_address,
                $this->city?->name,
                $this->governorate?->name,
                $this->country?->name,
            ])->filter()->implode(', ') ?: null,
        );
    }
}
