<?php

namespace App\Http\Resources\Ess;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeBasicResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => trim($this->first_name . ' ' . $this->last_name),
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'department' => $this->department ? $this->department->name : null,
            'designation' => $this->designation ? $this->designation->name : null,
            'date_of_appointment' => $this->date_of_appointment,
        ];
    }
}
