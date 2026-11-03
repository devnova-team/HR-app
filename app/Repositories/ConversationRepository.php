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
    
    public function getForEmployee(string $employeeId,int $limit):LengthAwarePaginator{
        return Conversation::whereHas('participants',function($q) use ($employeeId){
                $q->where('employees.id',$employeeId);
        })->with(['participants', 'latestMessage.sender'])
        ->latest('updated_at')
            ->paginate($limit);
    }

}
