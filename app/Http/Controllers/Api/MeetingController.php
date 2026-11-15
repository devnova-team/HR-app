<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MeetingResource;
use App\Services\MeetingService;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function __construct(protected MeetingService $service) {}

    public function today(Request $request)
    {
        return MeetingResource::collection($this->service->today($request->user()));
    }
}
