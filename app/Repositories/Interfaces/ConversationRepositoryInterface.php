<?php

namespace App\Repositories\Interfaces;
use App\Models\Conversation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface ConversationRepositoryInterface
{
    public function findById(string $id): ?Conversation;

    public function findByPairKey(string $pairKey): ?Conversation;

    public function findOrCreateByPairKey(string $pairKey): Conversation;
    public function create(array $data): Conversation;

    public function getForEmployee(string $employeeId, int $limit): LengthAwarePaginator;
}
