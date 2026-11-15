<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use App\Services\ConversationService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(protected ConversationService $service,protected ConversationRepositoryInterface $repo){}
    
    public function index(Request $request){
        $limit=min((int) $request->query('limit',10),100);
        $conversations = $this->repo->getForEmployee(
            (string) $request->user()->id,
            $limit
        );

        return ConversationResource::collection($conversations);
    }
    
    public function store(StoreConversationRequest $request){
        $conversation = $this->service->getOrCreateConversation(
            (string) $request->user()->id,
            $request->validated()['employee_id']
        );
        $conversation->load(['participants','latestMessage.sender']);
        return ApiResponse::success(
            new ConversationResource($conversation)
        );
    }
}
