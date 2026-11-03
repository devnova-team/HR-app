<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Employee;
use App\Repositories\MessageRepositoryInterface;
use App\Services\ConversationService;
use App\Services\MessageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_participant_cannot_send_message(): void
    {
        $conversation = Conversation::create([
            'pair_key' => 'employee-one:employee-two',
        ]);

        $sender = Employee::factory()->create();

        $service = app(MessageService::class);

        $this->expectException(AuthorizationException::class);

        $service->sendMessage(
            $conversation->id,
            $sender->id,
            [
                'type' => 'text',
                'body' => 'Hello',
            ]
        );
    }

    public function test_participant_can_send_text_message(): void
{
    $sender = Employee::factory()->create();
    $receiver = Employee::factory()->create();

    $conversation = Conversation::create([
        'pair_key' => collect([$sender->id, $receiver->id])
            ->sort()
            ->implode(':'),
    ]);

    $conversation->participants()->attach([
        $sender->id,
        $receiver->id,
    ]);

    $service = app(MessageService::class);

    $message = $service->sendMessage(
        $conversation->id,
        $sender->id,
        [
            'type' => 'text',
            'body' => 'Hello',
        ]
    );

    $this->assertDatabaseHas('messages', [
        'id' => $message->id,
        'conversation_id' => $conversation->id,
        'sender_id' => $sender->id,
        'type' => 'text',
        'body' => 'Hello',
    ]);
}

public function test_empty_text_message_is_rejected(): void
{
    $sender = Employee::factory()->create();
    $receiver = Employee::factory()->create();

    $conversation = Conversation::create([
        'pair_key' => collect([$sender->id, $receiver->id])
            ->sort()
            ->implode(':'),
    ]);

    $conversation->participants()->attach([
        $sender->id,
        $receiver->id,
    ]);

    $service = app(MessageService::class);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Text message body is required.');

    $service->sendMessage(
        $conversation->id,
        $sender->id,
        [
            'type' => 'text',
            'body' => '   ',
        ]
    );
}
public function test_invalid_message_type_is_rejected(): void
{
    $service = app(MessageService::class);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid message type.');

    $service->sendMessage(
        'unused-conversation-id',
        'unused-sender-id',
        [
            'type' => 'video',
            'body' => 'Hello',
        ]
    );
}
public function test_media_message_requires_media_url(): void
{
    $service = app(MessageService::class);

    foreach (['image', 'voice', 'document'] as $type) {
        try {
            $service->sendMessage(
                'unused-conversation-id',
                'unused-sender-id',
                [
                    'type' => $type,
                    'media_url' => '',
                ]
            );

            $this->fail("Expected {$type} message to require a media URL.");
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame('Media URL is required.', $exception->getMessage());
        }
    }
}
public function test_messages_can_be_retrieved_with_sender(): void
{
    $sender = Employee::factory()->create();
    $receiver = Employee::factory()->create();

    $conversation = Conversation::create([
        'pair_key' => collect([$sender->id, $receiver->id])
            ->sort()
            ->implode(':'),
    ]);

    $conversation->participants()->attach([
        $sender->id,
        $receiver->id,
    ]);

    $message = app(MessageService::class)->sendMessage(
        $conversation->id,
        $sender->id,
        [
            'type' => 'text',
            'body' => 'Hello',
        ]
    );

    $messages = app(MessageRepositoryInterface::class)
        ->getForConversation($conversation->id, 10);

    $this->assertTrue($messages->contains('id', $message->id));
    $this->assertTrue($messages->first()->relationLoaded('sender'));
    $this->assertEquals($sender->id, $messages->first()->sender->id);
}
public function test_same_employee_pair_gets_one_conversation(): void
{
    $employeeOne = Employee::factory()->create();
    $employeeTwo = Employee::factory()->create();

    $service = app(ConversationService::class);

    $firstConversation = $service->getOrCreateConversation(
        $employeeOne->id,
        $employeeTwo->id
    );

    $secondConversation = $service->getOrCreateConversation(
        $employeeTwo->id,
        $employeeOne->id
    );

    $this->assertEquals($firstConversation->id, $secondConversation->id);

    $this->assertDatabaseCount('conversations', 1);
    $this->assertDatabaseCount('conversation_participants', 2);
}
public function test_sending_message_creates_notification_for_recipient_only(): void
{
    $sender = Employee::factory()->create();
    $receiver = Employee::factory()->create();

    $conversation = Conversation::create([
        'pair_key' => collect([$sender->id, $receiver->id])
            ->sort()
            ->implode(':'),
    ]);

    $conversation->participants()->attach([
        $sender->id,
        $receiver->id,
    ]);

    app(MessageService::class)->sendMessage(
        $conversation->id,
        $sender->id,
        [
            'type' => 'text',
            'body' => 'Hello',
        ]
    );

    $this->assertDatabaseHas('notifications', [
        'employee_id' => $receiver->id,
        'title' => 'New message',
        'body' => 'Hello',
        'type' => 'chat',
        'reference_id' => $conversation->id,
    ]);

    $this->assertDatabaseMissing('notifications', [
        'employee_id' => $sender->id,
        'type' => 'chat',
        'reference_id' => $conversation->id,
    ]);
}
public function test_sending_image_message_creates_notification_for_recipient(): void
{
    $sender = Employee::factory()->create();
    $receiver = Employee::factory()->create();

    $conversation = Conversation::create([
        'pair_key' => collect([$sender->id, $receiver->id])
            ->sort()
            ->implode(':'),
    ]);

    $conversation->participants()->attach([
        $sender->id,
        $receiver->id,
    ]);

    app(MessageService::class)->sendMessage(
        $conversation->id,
        $sender->id,
        [
            'type' => 'image',
            'media_url' => 'https://example.com/image.jpg',
        ]
    );

    $this->assertDatabaseHas('notifications', [
        'employee_id' => $receiver->id,
        'title' => 'New message',
        'body' => 'Sent an image',
        'type' => 'chat',
        'reference_id' => $conversation->id,
    ]);
}
}