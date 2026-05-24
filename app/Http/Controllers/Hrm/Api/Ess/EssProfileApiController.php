<?php

namespace App\Http\Controllers\Hrm\Api\Ess;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Ess\EmployeeProfileResource;
use App\Models\Hrm\Employee;
use App\Models\Hrm\Province;
use App\Models\Hrm\Country;

class EssProfileApiController extends Controller
{
    /**
     * Get the authenticated employee's profile
     * 
     * @response 200 {
     *   "status": 1,
     *   "message": "Profile retrieved successfully",
     *   "data": {...}
     * }
     */
    public function index(Request $request)
    {
        try {
            $employee = $request->ess_employee;

            // Reload with relationships
            $employee = Employee::with(['department', 'designation'])
                ->find($employee->id);

            return response()->json([
                'status' => 1,
                'message' => 'Profile retrieved successfully',
                'data' => new EmployeeProfileResource($employee)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to retrieve profile',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the authenticated employee's profile
     * 
     * Editable fields: salutation, first_name, last_name, phone_number, date_of_birth, gender,
     * flat_no, street, city, state, country, pincode, emergency contacts (multiple)
     */
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                // Personal Information
                'salutation' => 'nullable|string|in:Mr,Mrs,Ms,Dr',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'date_of_birth' => 'nullable|date|before:today',
                'gender' => 'nullable|string|in:male,female,other',
                
                // Permanent Address
                'flat_no' => 'nullable|string|max:100',
                'street' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'state' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'pincode' => 'nullable|string|max:20',
                
                // Emergency Contacts (array support)
                'emergency_contact_name' => 'nullable|array',
                'emergency_contact_name.*' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|array',
                'emergency_contact_phone.*' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee = $request->ess_employee;

            // Allowed fields for update (matching web portal)
            $allowedFields = [
                'salutation',
                'first_name',
                'last_name',
                'phone_number',
                'date_of_birth',
                'gender',
                'flat_no',
                'street',
                'city',
                'state',
                'country',
                'pincode',
            ];

            // Prepare update data
            $updateData = [];
            foreach ($allowedFields as $field) {
                if ($request->has($field)) {
                    $updateData[$field] = $request->input($field);
                }
            }

            // Handle emergency contacts - convert arrays to comma-separated strings (same as web portal)
            if ($request->has('emergency_contact_name') && $request->has('emergency_contact_phone')) {
                // Filter out empty entries
                $names = array_filter($request->input('emergency_contact_name', []), fn($name) => !empty(trim($name)));
                $phones = array_filter($request->input('emergency_contact_phone', []), fn($phone) => !empty(trim($phone)));
                
                // Re-index arrays and join with commas
                $updateData['emergency_contact_name'] = !empty($names) ? implode(',', array_values($names)) : null;
                $updateData['emergency_contact_phone'] = !empty($phones) ? implode(',', array_values($phones)) : null;
            }

            $employee->update($updateData);

            // Reload employee with relationships
            $employee = Employee::with(['department', 'designation'])
                ->find($employee->id);

            return response()->json([
                'status' => 1,
                'message' => 'Profile updated successfully',
                'data' => new EmployeeProfileResource($employee)
            ], 200);

        } catch (\Exception $e) {
            \Log::error('ESS Profile Update Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 0,
                'message' => 'Failed to update profile',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get list of countries
     */
    public function getCountries(Request $request)
    {
        try {
            $countries = Country::where('status', 'Active')
                ->orderBy('name')
                ->get()
                ->map(function ($country) {
                    return [
                        'id' => $country->id,
                        'name' => $country->name,
                        'code' => $country->code ?? null,
                    ];
                });

            return response()->json([
                'status' => 1,
                'message' => 'Countries retrieved successfully',
                'data' => $countries
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to retrieve countries',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get provinces/states for a country
     */
    public function getProvinces(Request $request, $country_id)
    {
        try {
            // Get provinces for this country
            $provinces = Province::where('country_id', $country_id)
                ->orderBy('name')
                ->get()
                ->map(function ($province) {
                    return [
                        'id' => $province->id,
                        'name' => $province->name,
                        'code' => $province->code ?? null,
                    ];
                });

            return response()->json([
                'status' => 1,
                'message' => 'Provinces retrieved successfully',
                'data' => $provinces
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to retrieve provinces',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

}
