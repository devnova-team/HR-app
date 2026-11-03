<?php

namespace App\Repositories;

use App\Models\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MessageRepository implements MessageRepositoryInterface
{
    public function getForConversation(string $conversationId,int $limit): LengthAwarePaginator {
        return Message::where('conversation_id', $conversationId)
            ->with('sender')
                ->orderByDesc('created_at')
                    ->paginate($limit);
    }

    public function create(array $data): Message
    {
        return Message::create($data);
    }
}