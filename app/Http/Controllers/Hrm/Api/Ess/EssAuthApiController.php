<?php

namespace App\Http\Controllers\Hrm\Api\Ess;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Http\Resources\Ess\EmployeeBasicResource;
use App\Models\Hrm\Employee;
use App\Models\Hrm\EssRefreshToken;
use App\Mail\EssPasswordResetOtpMail;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Carbon\Carbon;

class EssAuthApiController extends Controller
{
    /**
     * Employee login
     * 
     * @bodyParam email string required The employee's email address. Example: john@company.com
     * @bodyParam password string required The employee's password. Example: password123
     * 
     * @response 200 {
     *   "status": 1,
     *   "message": "Login successful",
     *   "data": {
     *     "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     *     "expires_in": 3600,
     *     "employee": {...}
     *   }
     * }
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string|min:6',
                'device_name' => 'nullable|string|max:255',
                'device_type' => 'nullable|string|in:ios,android',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find employee by email
            $employee = Employee::where('email', $request->email)->first();

            if (!$employee) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid credentials. Please check your email and password.',
                    'error_code' => 'INVALID_CREDENTIALS'
                ], 401);
            }

            // Check if ESS is enabled
            if (!$employee->ess_enabled) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Your Employee Self-Service access has not been activated. Please contact HR.',
                    'error_code' => 'ESS_NOT_ENABLED'
                ], 403);
            }

            // Check if password is set
            if (!$employee->password) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Please set up your account using the setup link sent to your email.',
                    'error_code' => 'PASSWORD_NOT_SET'
                ], 403);
            }

            // Verify password
            if (!Hash::check($request->password, $employee->password)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid credentials. Please check your email and password.',
                    'error_code' => 'INVALID_CREDENTIALS'
                ], 401);
            }

            // Generate short-lived access token (JWT)
            $accessToken = auth('ess-api')->login($employee);

            // Generate long-lived refresh token (stored in database)
            $refreshToken = EssRefreshToken::generate($employee->id, 30, [
                'device_name' => $request->device_name,
                'device_type' => $request->device_type,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Update last login timestamp
            $employee->update(['ess_last_login_at' => now()]);

            return response()->json([
                'status' => 1,
                'message' => 'Login successful',
                'data' => [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken->token,
                    'expires_in' => auth('ess-api')->factory()->getTTL() * 60, // Access token TTL in seconds
                    'refresh_expires_in' => 30 * 24 * 60 * 60, // 30 days in seconds
                    'employee' => $this->formatEmployeeData($employee)
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('ESS Login Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'email' => $request->email
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'An error occurred during login. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Logout - Invalidate both access and refresh tokens
     */
    public function logout(Request $request)
    {
        try {
            $employee = auth('ess-api')->user();
            
            // Invalidate JWT access token
            auth('ess-api')->logout();

            // Revoke all refresh tokens for this employee
            if ($employee) {
                EssRefreshToken::revokeAllForEmployee($employee->id);
            }

            return response()->json([
                'status' => 1,
                'message' => 'Successfully logged out from all devices'
            ], 200);

        } catch (JWTException $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to logout. Please try again.'
            ], 500);
        }
    }

    /**
     * Refresh access token using refresh token
     * 
     * @bodyParam refresh_token string required The refresh token from login. Example: abc123...
     */
    public function refresh(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'refresh_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the refresh token
            $refreshToken = EssRefreshToken::where('token', $request->refresh_token)->first();

            if (!$refreshToken) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid refresh token',
                    'error_code' => 'INVALID_REFRESH_TOKEN'
                ], 401);
            }

            // Validate the refresh token
            if (!$refreshToken->isValid()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Refresh token has expired or been revoked. Please login again.',
                    'error_code' => 'REFRESH_TOKEN_EXPIRED'
                ], 401);
            }

            // Get the employee
            $employee = $refreshToken->employee;

            if (!$employee) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Employee not found',
                    'error_code' => 'EMPLOYEE_NOT_FOUND'
                ], 404);
            }

            // Check if ESS is still enabled
            if (!$employee->ess_enabled) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Your Employee Self-Service access has been disabled. Please contact HR.',
                    'error_code' => 'ESS_DISABLED'
                ], 403);
            }

            // Generate new access token
            $newAccessToken = auth('ess-api')->login($employee);

            // Mark refresh token as used
            $refreshToken->markAsUsed();

            return response()->json([
                'status' => 1,
                'message' => 'Access token refreshed successfully',
                'data' => [
                    'access_token' => $newAccessToken,
                    'expires_in' => auth('ess-api')->factory()->getTTL() * 60,
                    'employee' => $this->formatEmployeeData($employee)
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('ESS Token Refresh Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Could not refresh token. Please login again.',
                'error_code' => 'TOKEN_REFRESH_FAILED',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ], [
                'new_password.min' => 'New password must be at least 8 characters.',
                'new_password.confirmed' => 'Password confirmation does not match.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee = $request->ess_employee;

            // Verify current password
            if (!Hash::check($request->current_password, $employee->password)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Current password is incorrect.',
                    'error_code' => 'WRONG_PASSWORD'
                ], 422);
            }

            // Check if new password is same as current
            if (Hash::check($request->new_password, $employee->password)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'New password must be different from current password.',
                    'error_code' => 'SAME_PASSWORD'
                ], 422);
            }

            // Update password
            $employee->forceFill([
                'password' => Hash::make($request->new_password)
            ])->save();

            return response()->json([
                'status' => 1,
                'message' => 'Password changed successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'An error occurred. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Send password reset OTP via email
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee = Employee::where('email', $request->email)->first();

            // Always return success to prevent email enumeration
            if (!$employee || !$employee->ess_enabled) {
                return response()->json([
                    'status' => 1,
                    'message' => 'If your email exists in our system, you will receive a 6-digit OTP shortly.'
                ], 200);
            }

            // Generate 6-digit OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $employee->update([
                'ess_setup_token' => $otp,
                'ess_setup_token_expires_at' => now()->addMinutes(10), // OTP valid for 10 minutes
            ]);

            // Send OTP via email
            try {
                setCompanyConfigEmailForEmployee($employee);
                Mail::to($employee->email)->send(new EssPasswordResetOtpMail($employee, $otp, 10));
            } catch (\Exception $e) {
                \Log::error('Failed to send OTP email: ' . $e->getMessage());
                // Don't expose email failure to prevent enumeration
            }

            return response()->json([
                'status' => 1,
                'message' => 'If your email exists in our system, you will receive a 6-digit OTP shortly.',
                'data' => [
                    'expires_in' => 600 // 10 minutes in seconds
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'An error occurred. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Verify 6-digit OTP
     */
    public function verifyResetToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'otp' => 'required|string|digits:6',
            ], [
                'otp.required' => 'OTP is required.',
                'otp.digits' => 'OTP must be exactly 6 digits.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee = Employee::where('email', $request->email)
                ->where('ess_setup_token', $request->otp)
                ->where('ess_setup_token_expires_at', '>', now())
                ->first();

            if (!$employee) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid or expired OTP. Please request a new one.',
                    'error_code' => 'INVALID_OTP'
                ], 422);
            }

            return response()->json([
                'status' => 1,
                'message' => 'OTP is valid',
                'data' => [
                    'email' => $employee->email,
                    'valid_until' => $employee->ess_setup_token_expires_at->toIso8601String()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'An error occurred. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Reset password using OTP
     */
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'otp' => 'required|string|digits:6',
                'password' => 'required|string|min:8|confirmed',
            ], [
                'otp.required' => 'OTP is required.',
                'otp.digits' => 'OTP must be exactly 6 digits.',
                'password.min' => 'Password must be at least 8 characters.',
                'password.confirmed' => 'Password confirmation does not match.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee = Employee::where('email', $request->email)
                ->where('ess_setup_token', $request->otp)
                ->where('ess_setup_token_expires_at', '>', now())
                ->first();

            if (!$employee) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Invalid or expired OTP. Please request a new one.',
                    'error_code' => 'INVALID_OTP'
                ], 422);
            }

            // Update password and clear OTP
            $employee->forceFill([
                'password' => Hash::make($request->password),
                'ess_setup_token' => null,
                'ess_setup_token_expires_at' => null,
                'ess_enabled' => true,
            ])->save();

            return response()->json([
                'status' => 1,
                'message' => 'Password reset successfully. You can now login with your new password.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'An error occurred. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Format employee data for API response
     */
    private function formatEmployeeData(Employee $employee): array
    {
        return (new EmployeeBasicResource($employee))->resolve();
    }
}
