<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MsmeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'juridical' => [
                'id' => $this->id,
                'entity_no' => $this->entity_no,
                'name' => $this->name,
                'date_reg' => $this->date_reg,
                'registration_type' => $this->registration_type,
                'bus_status' => $this->bus_status,
                'capitalization' => $this->capitalization,
                'category' => $this->category,
                'contact_no' => $this->contact_no,
                'contact_email' => $this->contact_email,
                'line_of_industry' => $this->line_of_industry,
                'address_id' => $this->address->address_id,
                'region' => $this->address->region,
                'province' => $this->address->province,
                'city' => $this->address->city,
                'barangay' => $this->address->barangay,
                'subdivision' => $this->address->subdivision,
                'street' => $this->address->street,
                'upblb_num' => $this->address->upblb_num,
                'zip' => $this->address->zip,
                'longitude' => $this->address->longitude,
                'latitude' => $this->address->latitude,
            ],
            'employer' => [
                    'employer_id' => $this->employer->id,
                    'entity_no' => $this->employer->entity_no,
                    'full_name' => $this->employer->full_name,
                    'gender' => $this->employer->gender,
                    'birth_date' => $this->employer->birth_date,
                    'contact_no' => $this->employer->contact_no,
                    'email' => $this->employer->email,
                    'special_category' => $this?->employer->special_category,
                    'address_id' => $this->employer->address->id,
                    'region' => $this->employer->address->region,
                    'province' => $this->employer->address->province,
                    'city' => $this->employer->address->city,
                    'barangay' => $this->employer->address->barangay,
                    'subdivision' => $this->employer->address->subdivision,
                    'street' => $this->employer->address->street,
                    'upblb_num' => $this->employer->address->upblb_num,
                    'zip' => $this->employer->address->zip,
                    'longitude' => $this->employer->address->longitude,
                    'latitude' => $this->employer->address->latitude,
                ],
        ];
    }
}
