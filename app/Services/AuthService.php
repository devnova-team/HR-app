<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\RefreshToken;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function login(string $employeeId, string $password): array
    {
        $employee = Employee::where('employee_id', $employeeId)->first();

        if (! $employee || ! Hash::check($password, $employee->password) || ! $employee->is_active) {
            throw new AuthenticationException('Invalid credentials or inactive account.');
        }

        return DB::transaction(function () use ($employee) {
            $isFirstLogin = $employee->is_first_login;

            if ($isFirstLogin) {
                $employee->update(['is_first_login' => false]);
            }

            [$accessToken, $refreshToken] = $this->issueTokens($employee);

            $employee->load(['level', 'jobTitle', 'country', 'governorate', 'city']);

            return [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'is_first_login' => $isFirstLogin,
                // TODO: $employee->hasRole('hr') ? 'hr' : 'employee' after Spatie is merged
                'role' => 'employee',
                'missingFields' => $this->missingFields($employee),
                'employee' => $employee,
            ];
        });
    }

    // Refresh the access and refresh tokens using a valid refresh token
    public function refreshAccessTokens(string $plainRefreshToken): array
    {
        $result = DB::transaction(function () use ($plainRefreshToken) {
            $token = RefreshToken::where('token_hash', hash('sha256', trim($plainRefreshToken)))
                ->lockForUpdate()
                ->first();

            if (! $token) {
                return null;
            }

            $employee = $token->employee;

            // Check if the token has been revoked
            if ($token->revoked_at !== null) {
                $employee->refreshTokens()->whereNull('revoked_at')->update(['revoked_at' => now()]);
                $employee->tokens()->delete();

                return null;
            }

            if ($token->expires_at->isPast() || ! $employee->is_active) {
                return null;
            }

            // Revoke the old refresh token
            $token->update(['revoked_at' => now()]);

            [$accessToken, $refreshToken] = $this->issueTokens($employee);

            return [
                'accessToken' => $accessToken,
                'refreshToken' => $refreshToken,
            ];
        });

        // Throw AFTER the transaction so the revocations above are committed
        if (! $result) {
            throw new AuthenticationException('Invalid refresh token.');
        }

        return $result;
    }

    // make a new pair of access and refresh tokens for the given employee
    private function issueTokens(Employee $employee): array
    {
        $accessToken = $employee->createToken(
            'access',
            ['access'],
            now()->addMinutes((int) config('hr.access_token_ttl_minutes'))
        )->plainTextToken;

        $refreshToken = Str::random(64);

        $employee->refreshTokens()->create([
            'token_hash' => hash('sha256', $refreshToken),
            'expires_at' => now()->addDays((int) config('hr.refresh_token_ttl_days')),
        ]);

        // Return the tokens in an ordered array for destructuring
        return [$accessToken, $refreshToken];
    }

    private function missingFields(Employee $employee): array
    {
        $missing = [];

        if (! $employee->date_of_birth) {
            $missing[] = 'dateOfBirth';
        }

        if (! $employee->getRawOriginal('avatar_url')) {
            $missing[] = 'avatarUrl';
        }

        return $missing;
    }

    // Logout the user by revoking the refresh token and deleting the current access token
    public function logout(Employee $employee, string $plainTextToken): void
    {
        $employee->refreshTokens()->where('token_hash', hash('sha256', trim($plainTextToken)))->whereNull('revoked_at')->update(['revoked_at' => now()]);
        $employee->currentAccessToken()->delete();
    }
}
