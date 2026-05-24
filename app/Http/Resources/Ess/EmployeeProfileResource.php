<?php

namespace App\Http\Resources\Ess;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'salutation' => $this->salutation,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => trim(($this->salutation ? $this->salutation . ' ' : '') . $this->first_name . ' ' . $this->last_name),
            'email' => $this->email,
            'date_of_birth' => $this->date_of_birth ? $this->date_of_birth->format('Y-m-d') : null,
            'gender' => $this->gender,
            'phone_number' => $this->phone_number,
            'department' => $this->department ? [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ] : null,
            'designation' => $this->designation ? [
                'id' => $this->designation->id,
                'name' => $this->designation->name,
            ] : null,
            'date_of_appointment' => $this->date_of_appointment,
            'identification_type' => $this->identification_type,
            'id_number' => $this->id_number,
            'passport_country' => $this->passport_country,
            'tax_reference_number' => $this->tax_reference_number,
            'permanent_address' => [
                'flat_no' => $this->flat_no,
                'street' => $this->street,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'pincode' => $this->pincode,
            ],
            'emergency_contacts' => $this->parseEmergencyContacts(),
        ];
    }

    private function parseEmergencyContacts(): array
    {
        $names = $this->emergency_contact_name ? explode(',', $this->emergency_contact_name) : [];
        $phones = $this->emergency_contact_phone ? explode(',', $this->emergency_contact_phone) : [];

        $contacts = [];
        $maxCount = max(count($names), count($phones));

        for ($index = 0; $index < $maxCount; $index++) {
            $name = trim($names[$index] ?? '');
            $phone = trim($phones[$index] ?? '');

            if (!empty($name) || !empty($phone)) {
                $contacts[] = [
                    'name' => $name,
                    'phone' => $phone,
                ];
            }
        }

        return $contacts;
    }
}
