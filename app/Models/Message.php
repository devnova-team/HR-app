<?php

namespace App\Models;

use App\Models\Conversation;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasUuids;
    public $timestamps = false;
    const CREATED_AT='created_at';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'type',
        'body',
        'media_url',
        'file_name',
        'duration_seconds',
        'created_at',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'sender_id');
    }

   
}
