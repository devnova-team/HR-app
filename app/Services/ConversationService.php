<?php

namespace App\Services;

use App\Repositories\Interfaces\ConversationRepositoryInterface;
use Illuminate\Support\Facades\DB;
class ConversationService
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected ConversationRepositoryInterface $repo){}

    public function getOrCreateConversation(string $employeeOneId, string $employeeTwoId)
    {
        $pairKey=collect([$employeeOneId,$employeeTwoId])
            ->sort()
                ->implode(':');
        if ($employeeOneId === $employeeTwoId) {
            throw new \InvalidArgumentException('An employee cannot create a conversation with themselves.');
        }

        return DB::transaction(function() use($pairKey,$employeeOneId,$employeeTwoId){
            $conversation=$this->repo->findOrCreateByPairKey($pairKey);
            if($conversation->wasRecentlyCreated){
                $conversation->participants()->attach([
                    $employeeOneId,
                    $employeeTwoId,
                ]);
            }
            return $conversation;
        });
    }
}
