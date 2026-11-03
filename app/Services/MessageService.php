<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\MessageRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
class MessageService
{
    public function __construct(
        protected MessageRepositoryInterface $repo,
        protected NotificationService $notificationService
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
        // Get the other participant
            $recipient = $conversation->participants()
            ->where('employees.id', '!=', $senderId)
            ->first();

            if (!$recipient) {
            throw new \RuntimeException('Conversation recipient not found.');
            }

       // 4. Create message and notification in one transaction
    return DB::transaction(function () use ($data, $conversationId, $senderId, $conversation, $recipient) {
            $message = $this->repo->create([
                ...$data,
                'conversation_id' => $conversationId,
                'sender_id' => $senderId,
                'created_at' => now(),
            ]);
            $conversation->touch();
            $notificationBody = match ($data['type']) {
                'text' => $data['body'],
                'image' => 'Sent an image',
                'voice' => 'Sent a voice message',
                'document' => 'Sent a document',
            };
            $this->notificationService->send(
                $recipient,
                'New message',
                $notificationBody,
                'chat',
                $conversationId
            );
            return $message;
        });
    }
}