<?php

namespace App\Repositories;

use App\Models\Conversation;
use App\Repositories\ConversationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ConversationRepository implements ConversationRepositoryInterface{
    public function findById(string $id): ?Conversation
    {
        return Conversation::find($id);
    }

    public function findByPairKey(string $pairKey): ?Conversation
    {
        return Conversation::where('pair_key', $pairKey)->first();
    }
    public function findOrCreateByPairKey(string $pairKey): Conversation{
        return Conversation::firstOrCreate([
            'pair_key' => $pairKey,
        ]);
    }

    public function create(array $data): Conversation
    {
        return Conversation::create($data);
    }
    
    public function getForEmployee(string $employeeId, int $limit): LengthAwarePaginator
{
    return Conversation::whereHas('participants', function ($q) use ($employeeId) {
            $q->where('employees.id', $employeeId);
        })
        ->with(['participants', 'latestMessage.sender'])
        ->withCount([
            'messages as unread_count' => function ($q) use ($employeeId) {
                $q->where('sender_id', '!=', $employeeId)
                    ->whereExists(function ($subQuery) use ($employeeId) {
                        $subQuery->selectRaw('1')
                            ->from('conversation_participants as cp')
                            ->whereColumn('cp.conversation_id', 'messages.conversation_id')
                            ->where('cp.employee_id', $employeeId)
                            ->where(function ($readQuery) {
                                $readQuery->whereNull('cp.last_read_at')
                                    ->orWhereColumn(
                                        'messages.created_at',
                                        '>',
                                        'cp.last_read_at'
                                    );
                            });
                    });
            },
        ])
        ->latest('updated_at')
        ->paginate($limit);
}

}
