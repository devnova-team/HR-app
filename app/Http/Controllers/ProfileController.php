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

    /** GET /me */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => (new EmployeeResource($this->employee($request)))->resolve(),
        ]);
    }

    /** GET /me/personal-data */
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

    /**
     * PUT /me/avatar
     *
     * The DB stores the RELATIVE path (avatars/xxx.jpg); the Employee::avatarUrl
     * accessor turns it into a full URL on read, so the URL is built in one place only.
     */
    public function updateAvatar(UpdateAvatarRequest $request): JsonResponse
    {
        $employee = $request->user();
        $file = $request->file('photo');

        $oldPath = $employee->getRawOriginal('avatar_url');

        $path = $file->storeAs(
            'avatars',
            $employee->id.'-'.Str::random(12).'.'.$file->extension(),
            'public',
        );

        $employee->forceFill(['avatar_url' => $path])->save();
        $this->deleteOldAvatar($oldPath);

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

    /** Delete the previous avatar from disk, only if it is one of ours (avatars/ folder). */
    private function deleteOldAvatar(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'avatars/')) {
            return; // empty, or not one of ours (e.g. a seeded external URL)
        }

        Storage::disk('public')->delete($path);
    }
}