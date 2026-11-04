<?php

namespace App\Models;

class PasswordResetOtp extends BaseModel
{
    protected $fillable = [
        'employee_id',
        'otp_hash',
        'expires_at',
        'verified_at',
        'used_at',
        'attempts',
        'reset_token_hash',
        'reset_token_expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'used_at' => 'datetime',
        'attempts' => 'integer',
        'reset_token_expires_at' => 'datetime',
    ];

    protected $hidden = [
        'otp_hash',
        'reset_token_hash',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
