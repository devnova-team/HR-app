<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Repositories\MessageRepositoryInterface;
use App\Services\MessageService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        protected MessageService $service,
        protected MessageRepositoryInterface $repository
    ) {}

    public function index(Request $request, string $conversationId)
    {
        $limit = min((int) $request->query('limit', 20), 100);
        abort_unless(
            $request->user()
                ->conversations()
                ->where('conversations.id', $conversationId)
                ->exists(),
            403,
            'You are not a participant in this conversation.'
        );
        $messages = $this->repository->getForConversation(
            $conversationId,
            $limit
        );
        return MessageResource::collection($messages);
    }

    public function store(StoreMessageRequest $request,string $conversationId) {
        $message = $this->service->sendMessage(
            $conversationId,
            (string) $request->user()->id,
            $request->validated()
        );
        $message->load('sender');

        return ApiResponse::created(
            new MessageResource($message)
        );
    }

    public function markAsRead(Request $request, string $conversationId)
{
    abort_unless(
        $request->user()
            ->conversations()
            ->where('conversations.id', $conversationId)
            ->exists(),
        403,
        'You are not a participant in this conversation.'
    );
    $request->user()->conversations()->updateExistingPivot(
        $conversationId,
        ['last_read_at' => now()]
    );
    return ApiResponse::noContent();
}
}