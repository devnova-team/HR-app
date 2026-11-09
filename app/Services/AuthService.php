<?php

namespace App\Services;

use App\Mail\PasswordResetOtpMail;
use App\Models\Employee;
use App\Models\PasswordResetOtp;
use App\Models\RefreshToken;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
                'role' => $employee->hasRole('hr') ? 'hr' : 'employee',
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

    // change the password for the given employee, and revoke all refresh tokens
    public function changePassword(Employee $employee, string $currentPassword, string $newPassword): void
    {
        // Check if the current password is correct
        if (! Hash::check($currentPassword, $employee->password)) {
            throw ValidationException::withMessages([
                'currentPassword' => ['The current password is incorrect.'],
            ]);
        }
        // Revoke every refresh token, then delete every access token
        DB::transaction(function () use ($employee, $newPassword) {
            $employee->update(['password' => $newPassword]);  // hashed by the cast in Employee model
            $employee->refreshTokens()->whereNull('revoked_at')->update(['revoked_at' => now()]);
            $employee->tokens()->delete();
        });
    }

    //Forget password to reset the password for employee by sending a reset link to the email
    public function forgetPassword(string $email): void
    {
        $employee = Employee::where('email', $email)->where('is_active', true)->first();
        if (!$employee ) {
            return;
        }

        //create otp
        $otp = (string) random_int(100000, 999999);
        $ttl = (int) config('hr.otp_ttl_minutes');

        DB::transaction(function ()use($employee, $otp, $ttl) {
            $employee->passwordResetOtps()->whereNull('used_at')->update(['used_at' => now()]); // Revoke any previous unused OTPs
            $employee->passwordResetOtps()->create([
                'otp_hash' => Hash::make($otp),
                'expires_at' => now()->addMinutes($ttl),
            ]);
        });

        // Queue the email only after the OTP row is committed
        Mail::to($employee->email)->queue(new PasswordResetOtpMail($employee->first_name, $otp, $ttl));

    }

    // verify the OTP for the given email and reset token
    public function verifyOtp(string $email, string $otp)
    {
        $email  = Str::lower(trim($email));
        $maxAttempts = (int) config('hr.otp_max_attempts', 5);

        $resetToken = DB::transaction(function() use($email, $otp, $maxAttempts){
         $employee = Employee::where('email', $email)->where('is_active', true)->first();
        if (!$employee ) {
            return;
        }

        // get the latest unused OTP for the employee
        $otpRecord = $employee->passwordResetOtps()->whereNull('used_at')->whereNull('verified_at')->latest()->lockForUpdate()->first();

        // check if the OTP is valid and not expired and the attempts are less than 5
        if (!$otpRecord || $otpRecord->expires_at->isPast() || $otpRecord->attempts >= $maxAttempts) {
           return null;
        }

        if (!Hash::check($otp, $otpRecord->otp_hash)){
            $otpRecord->increment('attempts');

            // increment the attempts
            if ($otpRecord->attemtps >= $maxAttempts) {
                $otpRecord->update(['used_at' => now()]);
            }
            return null;
        }


        // create reset token
        $plainToken = Str::random(64);

        $otpRecord->update([
            'verified_at' => now(),
            'reset_token_hash' => hash('sha256', $plainToken),
            'reset_token_expires_at' => now()->addMinutes((int) config('hr.reset_token_ttl_minutes')),
        ]);

        return $plainToken;
    });

    // Throw AFTER the transaction so the failed-attempt increment is not rolled back
    if (! $resetToken) {
        throw ValidationException::withMessages([
            'otp' => ['Invalid or expired OTP.'],
        ]);
    }

    return $resetToken;

    }

    public function resetPassword( string $resetToken, string $newPassword):void
    {
        $resetTokenHash = hash('sha256', trim($resetToken));

        DB::transaction(function() use($resetTokenHash, $newPassword){

           // get the OTP record with the given reset token hash and check if it is valid and not expired
            $record = PasswordResetOtp::with('employee')->whereNotNull('verified_at')->where('reset_token_hash', $resetTokenHash)->whereNull('used_at')->lockForUpdate()->first();

            if (!$record || $record->reset_token_expires_at->isPast() || ! $record->employee->is_active) {
                throw ValidationException::withMessages([
                    'resetToken' => ['Invalid or expired reset token.'],
                ]);
            }

            // Update the employee's password, revoke all refresh tokens, and delete all access tokens
            $employee = $record->employee;
            $employee->update(['password' => $newPassword]);  // hashed by the cast in Employee model
            $employee->refreshTokens()->whereNull('revoked_at')->update(['revoked_at' => now()]);
            $employee->tokens()->delete();

            // Mark the reset token as used
            $employee->passwordResetOtps()->whereNull('used_at')->update(['used_at' => now()]);
        });
    }
}
