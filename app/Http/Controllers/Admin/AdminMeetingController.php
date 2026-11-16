<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMeetingRequest;
use App\Http\Resources\MeetingResource;
use App\Services\MeetingService;
use App\Support\ApiResponse;

class AdminMeetingController extends Controller
{
    public function __construct(protected MeetingService $service) {}

    public function store(StoreMeetingRequest $request)
    {
        return ApiResponse::created(new MeetingResource($this->service->create($request->validated())));
    }
}
