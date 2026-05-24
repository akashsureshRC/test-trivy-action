<?php

namespace App\Http\Controllers\Hrm\Ess;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Hrm\Department;
use App\Models\Hrm\Designation;
use App\Models\Hrm\Country;
use App\Models\Hrm\Province;

class EssProfileController extends Controller
{
    /**
     * Display the employee's profile.
     */
    public function index()
    {
        $employee = Auth::guard('employee')->user();
        
        // Parse emergency contacts from comma-separated strings
        $emergencyContacts = $this->parseEmergencyContacts($employee);
        
        return view('hrm.ess.profile.index', compact('employee', 'emergencyContacts'));
    }

    /**
     * Show the form for editing the employee's profile.
     */
    public function edit()
    {
        $employee = Auth::guard('employee')->user();
        
        // Parse emergency contacts from comma-separated strings
        $emergencyContacts = $this->parseEmergencyContacts($employee);
        
        // Get countries for dropdown
        $countries = Country::where('status', 'Active')->orderBy('name')->get();
        
        // Get provinces for current country
        $provinces = [];
        if ($employee->country) {
            $country = Country::where('name', $employee->country)->first();
            if ($country) {
                $provinces = Province::where('country_id', $country->id)->orderBy('name')->get();
            }
        }
        
        return view('hrm.ess.profile.edit', compact('employee', 'emergencyContacts', 'countries', 'provinces'));
    }

    /**
     * Update the employee's profile (limited fields).
     */
    public function update(Request $request)
    {
        $employee = Auth::guard('employee')->user();

        $validated = $request->validate([
            // Personal Information (excluding email)
            'salutation' => 'nullable|string|max:20',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            
            // Address fields
            'flat_no' => 'nullable|string|max:100',
            'street' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            
            // Emergency contacts (arrays)
            'emergency_contact_name' => 'nullable|array',
            'emergency_contact_name.*' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|array',
            'emergency_contact_phone.*' => 'nullable|string|max:20',
        ]);

        // Process emergency contacts - filter out empty entries
        $contactNames = array_filter($request->emergency_contact_name ?? [], fn($name) => !empty(trim($name)));
        $contactPhones = array_filter($request->emergency_contact_phone ?? [], fn($phone) => !empty(trim($phone)));
        
        // Re-index arrays and join with commas
        $validated['emergency_contact_name'] = !empty($contactNames) ? implode(',', array_values($contactNames)) : null;
        $validated['emergency_contact_phone'] = !empty($contactPhones) ? implode(',', array_values($contactPhones)) : null;

        $employee->update($validated);

        return redirect()->route('ess.profile')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Show the change password form.
     */
    public function showChangePassword()
    {
        return view('hrm.ess.profile.change-password');
    }

    /**
     * Update the employee's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $employee = Auth::guard('employee')->user();

        if (!Hash::check($request->current_password, $employee->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $employee->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return redirect()->route('ess.profile')
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Get employee's department and designation info.
     */
    public function getEmploymentInfo()
    {
        $employee = Auth::guard('employee')->user();
        
        $department = Department::find($employee->department_id);
        $designation = Designation::find($employee->designation_id);

        return response()->json([
            'department' => $department?->name ?? 'N/A',
            'designation' => $designation?->name ?? 'N/A',
            'employee_id' => $employee->employee_id,
            'date_of_appointment' => $employee->date_of_appointment,
        ]);
    }
    
    /**
     * Parse emergency contacts from comma-separated strings
     */
    private function parseEmergencyContacts($employee): array
    {
        $names = $employee->emergency_contact_name ? explode(',', $employee->emergency_contact_name) : [];
        $phones = $employee->emergency_contact_phone ? explode(',', $employee->emergency_contact_phone) : [];
        
        $contacts = [];
        $maxCount = max(count($names), count($phones));
        
        for ($i = 0; $i < $maxCount; $i++) {
            $contacts[] = [
                'name' => trim($names[$i] ?? ''),
                'phone' => trim($phones[$i] ?? ''),
            ];
        }
        
        return $contacts;
    }
    
    /**
     * Get provinces for a country (AJAX endpoint)
     */
    public function getProvinces($country)
    {
        $countryModel = Country::where('name', $country)->first();
        
        if (!$countryModel) {
            return response()->json([]);
        }
        
        $provinces = Province::where('country_id', $countryModel->id)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json($provinces);
    }
}
