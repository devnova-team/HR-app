<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAvatarRequest;
use App\Http\Requests\UpdatePersonalDataRequest;
use App\Http\Resources\EmployeeResource; 
use App\Http\Resources\PersonalDataResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /** Relations needed to build PersonalData and EmployeeResource (defined on Employee by Moaz). */
    private const PROFILE_RELATIONS = ['level', 'department', 'jobTitle', 'country', 'governorate', 'city'];

    
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => (new EmployeeResource($this->employee($request)))->resolve(),
        ]);
    }

    
    public function personalData(Request $request): JsonResponse
    {
        return $this->personalDataResponse($this->employee($request));
    }

    /** PATCH /me/personal-data: only firstName, lastName, dateOfBirth, fullAddress are editable. */
    public function updatePersonalData(UpdatePersonalDataRequest $request): JsonResponse
    {
        $employee = $request->user();

        $attributes = $request->attributesToUpdate();
        if ($attributes !== []) {
            $employee->forceFill($attributes)->save();
        }

        return $this->personalDataResponse($employee->refresh()->loadMissing(self::PROFILE_RELATIONS));
    }


    public function updateAvatar(UpdateAvatarRequest $request): JsonResponse
    {
        $employee = $request->user();
        $file = $request->file('photo');

        $path = $file->storeAs(
            'avatars',
            $employee->id.'-'.Str::random(12).'.'.$file->extension(),
            'public',
        );

        $oldUrl = $employee->avatar_url;
        $employee->forceFill(['avatar_url' => url(Storage::disk('public')->url($path))])->save();
        $this->deleteOldAvatar($oldUrl);

        return response()->json(['data' => ['avatarUrl' => $employee->avatar_url]]);
    }

    private function employee(Request $request): Employee
    {
        return $request->user()->loadMissing(self::PROFILE_RELATIONS);
    }

    private function personalDataResponse(Employee $employee): JsonResponse
    {
        return response()->json(['data' => (new PersonalDataResource($employee))->resolve()]);
    }

    private function deleteOldAvatar(?string $url): void
    {
        if (! $url) {
            return;
        }

        $prefix = url(Storage::disk('public')->url(''));
        if (! str_starts_with($url, $prefix)) {
            return; // not one of ours (e.g. a seeded external URL)
        }

        $relative = substr($url, strlen($prefix));
        if (str_starts_with($relative, 'avatars/')) {
            Storage::disk('public')->delete($relative);
        }
    }
}
