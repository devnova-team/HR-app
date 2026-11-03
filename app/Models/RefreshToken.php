<?php

namespace App\Models;

class RefreshToken extends BaseModel
{
    protected $fillable = [
        'employee_id',
        'token_hash',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected $hidden = [
        'token_hash',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
