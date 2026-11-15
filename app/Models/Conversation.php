<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasUuids;

    protected $fillable = [
        'pair_key',
    ];

    public function participants():BelongsToMany{
        return $this->belongsToMany(
            Employee::class,
            'conversation_participants',
            'conversation_id',
            'employee_id'
        )->withPivot('last_read_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasOne
{
    return $this->hasOne(Message::class)->latestOfMany('created_at');
}
}
