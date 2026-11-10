<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class Employee extends Authenticatable
{
    use HasApiTokens, HasUuids, HasRoles, HasFactory;

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
        'is_first_login' => 'boolean',
        'is_active' => 'boolean',
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

    // Notifications
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // Employee Work Schedules
    public function employeeWorkSchedules(): HasMany
    {
        return $this->hasMany(EmployeeWorkSchedule::class, 'employee_id');
    }

    // Attendance Records
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'employee_id');
    }

    // Conversations
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(
            Conversation::class,
            'conversation_participants',
            'employee_id',
            'conversation_id'
        )->withPivot('last_read_at');
    }

    // Sent Messages
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Push Tokens
    public function pushTokens(): HasMany
    {
        return $this->hasMany(EmployeePushToken::class);
    }

    // Password Reset OTPs
    public function passwordResetOtps(): HasMany
    {
        return $this->hasMany(PasswordResetOtp::class);
    }

    // Refresh Tokens
    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class);
    }

    // Tasks
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

    // Check if the employee can assign tasks.
    public function canAssignTasks(): bool
    {
        $minimumRank = (int) config('hr.task_min_assigner_rank', 3);

        return $this->level !== null
            && $this->level->rank >= $minimumRank;
    }

    // Get employees that can be assigned tasks by the given employee.
    public function scopeAssignableBy(
        Builder $query,
        Employee $assigner
    ): Builder {
        if (! $assigner->canAssignTasks()) {
            return $query->whereRaw('1 = 0');
        }

        $assignerRank = $assigner->level->rank;

        return $query
            ->where('employees.department_id', $assigner->department_id)
            ->where('employees.is_active', true)
            ->where('employees.id', '<>', $assigner->getKey())
            ->whereHas('level', function (Builder $levelQuery) use ($assignerRank) {
                $levelQuery->where('rank', '<', $assignerRank);
            });
    }

    // ---------- Accessors ----------

    // Full Name
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    // Avatar URL
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value
                ? Storage::disk('public')->url($value)
                : null,
        );
    }

    // Position Label
    protected function positionLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => trim("{$this->level?->name} {$this->jobTitle?->name}") ?: null,
        );
    }

    // Formatted Address
    protected function address(): Attribute
    {
        return Attribute::make(
            get: fn() => collect([
                $this->full_address,
                $this->city?->name,
                $this->governorate?->name,
                $this->country?->name,
            ])->filter()->implode(', ') ?: null,
        );
    }
}
