<?php

namespace App\Repositories\Interfaces;

use App\Models\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MessageRepositoryInterface
{
    public function getForConversation(string $conversationId, int $limit): LengthAwarePaginator;

    public function create(array $data): Message;
}