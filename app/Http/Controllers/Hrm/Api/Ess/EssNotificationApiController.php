<?php

namespace App\Http\Controllers\Hrm\Api\Ess;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Hrm\EssDeviceToken;

class EssNotificationApiController extends Controller
{
    /**
     * Register device token for push notifications.
     * 
     * @bodyParam fcm_token string required The Firebase Cloud Messaging token. Example: dK9x...
     * @bodyParam device_type string optional Device type (ios/android). Example: android
     * @bodyParam device_name string optional Device name. Example: Samsung Galaxy S21
     * 
     * @response 200 {
     *   "status": 1,
     *   "message": "Device registered successfully"
     * }
     */
    public function registerDevice(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fcm_token' => 'required|string',
                'device_type' => 'nullable|string|in:ios,android',
                'device_name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee = $request->ess_employee;

            EssDeviceToken::registerToken(
                $employee->id,
                $request->fcm_token,
                $request->device_type,
                $request->device_name
            );

            return response()->json([
                'status' => 1,
                'message' => 'Device registered successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to register device',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Unregister device token (e.g., on logout).
     * 
     * @bodyParam fcm_token string required The Firebase Cloud Messaging token to remove. Example: dK9x...
     * 
     * @response 200 {
     *   "status": 1,
     *   "message": "Device unregistered successfully"
     * }
     */
    public function unregisterDevice(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fcm_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee = $request->ess_employee;

            EssDeviceToken::where('employee_id', $employee->id)
                ->where('fcm_token', $request->fcm_token)
                ->delete();

            return response()->json([
                'status' => 1,
                'message' => 'Device unregistered successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to unregister device',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
