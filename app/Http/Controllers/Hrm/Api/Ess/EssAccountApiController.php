<?php

namespace App\Http\Controllers\Hrm\Api\Ess;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Hrm\EssRefreshToken;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class EssAccountApiController extends Controller
{
    /**
     * Delete user account (disable ESS access)
     * 
     * Complies with Google Play and App Store data deletion requirements.
     * This will:
     * - Disable ESS access (set ess_enabled to false)
     * - Revoke all refresh tokens
     * - Log the user out
     * 
     * The employee profile remains active for HR/payroll purposes.
     * To restore ESS access, HR must send a new ESS invitation.
     * 
     * @bodyParam password string required The user's current password for confirmation. Example: mypassword123
     * 
     * @response 200 {
     *   "status": 1,
     *   "message": "Account deleted successfully"
     * }
     * @response 401 {
     *   "status": 0,
     *   "message": "Incorrect password",
     *   "error_code": "INVALID_PASSWORD"
     * }
     */
    public function delete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee = $request->ess_employee;

            // Verify password
            if (!Hash::check($request->password, $employee->password)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Incorrect password. Please try again.',
                    'error_code' => 'INVALID_PASSWORD'
                ], 401);
            }

            // Revoke all refresh tokens for this user
            EssRefreshToken::where('employee_id', $employee->id)->delete();

            // Disable ESS access
            $employee->forceFill([
                'ess_enabled' => false,
            ])->save();

            // Invalidate the current JWT token
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status' => 1,
                'message' => 'Your account has been deleted successfully.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to delete account. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
