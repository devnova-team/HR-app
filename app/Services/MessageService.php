<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\MessageRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;

class MessageService
{
    public function __construct(
        protected MessageRepositoryInterface $repo
    ) {}

    public function sendMessage(string $conversationId,string $senderId,array $data): Message {
        // 1. Validate message type
        $allowedTypes = ['text', 'image', 'voice', 'document'];

        if (!in_array($data['type'] ?? null, $allowedTypes, true)) {
            throw new \InvalidArgumentException(
                'Invalid message type.'
            );
        }
        if ($data['type'] === 'text' && empty(trim($data['body'] ?? ''))) {
            throw new \InvalidArgumentException('Text message body is required.');
        }
        if (
            in_array($data['type'], ['image', 'voice', 'document'], true)
            && empty(trim($data['media_url'] ?? ''))
        ) {
            throw new \InvalidArgumentException('Media URL is required.');
        }

        // 2. Find conversation
        $conversation = Conversation::findOrFail($conversationId);

        // 3. Check if sender is a participant
        if (!$conversation->participants()
            ->where('employees.id', $senderId)
            ->exists()) {

            throw new AuthorizationException(
                'You are not a participant in this conversation.'
            );
        }

        // 4. Create message
        $message= $this->repo->create([
            ...$data,
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'created_at' => now(),
        ]);
        $conversation->touch();
        return $message;
    }
}