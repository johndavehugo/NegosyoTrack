<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MsmeManagement\Address;
use App\Models\MsmeManagement\Employer;
use App\Models\MsmeManagement\Juridical;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class MsmeController extends Controller
{
    public function index(Request $request): JsonResponse{

        $query = Juridical::with(['address', 'employer.address']);

        $search = $request->query('search');
        if ($search) {
            $query->where('name', 'like', "%$search%");
        }

        $rows = $query->get();

        $total = $rows->count();

        $businesses = $rows->map(function ($row) {
            return [
                'juridical' => [
                    'id' => $row->id,
                    'entity_no' => $row->entity_no,
                    'name' => $row->name,
                    'date_reg' => $row->date_reg,
                    'registration_type' => $row->registration_type,
                    'bus_status' => $row->bus_status,
                    'capitalization' => $row->capitalization,
                    'category' => $row->category,
                    'contact_no' => $row->contact_no,
                    'contact_email' => $row->contact_email,
                    'line_of_industry' => $row->line_of_industry,
                    'address_id' => $row->address?->id,
                    'region' => $row->address?->region,
                    'province' => $row->address?->province,
                    'city' => $row->address?->city,
                    'barangay' => $row->address?->barangay,
                    'subdivision' => $row->address?->subdivision,
                    'street' => $row->address?->street,
                    'upblb_num' => $row->address?->upblb_num,
                    'zip' => $row->address?->zip,
                    'longitude' => $row->address?->longitude,
                    'latitude' => $row->address?->latitude,
                ],
                'employer' => [
                    'employer_id' => $row->employer?->id,
                    'entity_no' => $row->employer?->entity_no,
                    'full_name' => $row->employer?->full_name,
                    'gender' => $row->employer?->gender,
                    'birth_date' => $row->employer?->birth_date,
                    'contact_no' => $row->employer?->contact_no,
                    'email' => $row->employer?->email,
                    'special_category' => $row?->employer?->special_category,
                    'address_id' => $row->employer?->address->id,
                    'region' => $row->employer?->address->region,
                    'province' => $row->employer?->address->province,
                    'city' => $row->employer?->address->city,
                    'barangay' => $row->employer?->address->barangay,
                    'subdivision' => $row->employer?->address->subdivision,
                    'street' => $row->employer?->address->street,
                    'upblb_num' => $row->employer?->address->upblb_num,
                    'zip' => $row->employer?->address->zip,
                    'longitude' => $row->employer?->address->longitude,
                    'latitude' => $row->employer?->address->latitude,
                ],
            ];
        });

        return response()->json([
            'status' => 'success',
            'type' => 'business_list',
            'total' => $total,
            'data' => $businesses,
        ]);
    }

    public function store(Request $request): JsonResponse{
        $validated = $request->validate([
            //Business
                'juri_entity_no' => 'required|string',
                'juri_name' => 'required|string',
                'juri_date_reg' => 'required|date',
                'juri_capitalization' => 'numeric',
                'juri_contact_no' => 'nullable|string|max:30',
                'juri_contact_email' => 'nullable|email|max:150',
                'juri_line_of_industry' => 'required|string',
                'juri_region' => 'nullable|string|max:100',
                'juri_province' => 'nullable|string|max:100',
                'juri_city' => 'nullable|string|max:100',
                'juri_barangay' => 'nullable|string|max:100',
                'juri_subdivision' => 'nullable|string|max:100',
                'juri_street' => 'nullable|string|max:150',
                'juri_upblb_num' => 'nullable|string|max:50',
                'juri_zip' => 'nullable|string|max:15',

            //Employer           
                'emp_entity_no' => 'required|string',
                'emp_full_name' => 'required|string',
                'emp_gender' => 'required|string',
                'emp_birth_date' => 'required|date',
                'emp_contact_no' => 'nullable|string|max:30',
                'emp_email' => 'nullable|email|max:150',
                'emp_special_category' => 'required|string|max:255',
                'emp_region' => 'nullable|string|max:100',
                'emp_province' => 'nullable|string|max:100',
                'emp_city' => 'nullable|string|max:100',
                'emp_barangay' => 'nullable|string|max:100',
                'emp_subdivision' => 'nullable|string|max:100',
                'emp_street' => 'nullable|string|max:150',
                'emp_upblb_num' => 'nullable|string|max:50',
                'emp_zip' => 'nullable|string|max:15',

            ]);

            DB::transaction(function() use($validated){
                $juri_id = 'jur-' . Str::uuid7();
                $emp_id = 'emp-' . Str::uuid7();
                $emp_address_id = 'addr-' . Str::uuid7();
                $juri_address_id = 'addr-' . Str::uuid7();

                Address::create([
                    'id' => $juri_address_id,
                    'region' => $validated['juri_region'],
                    'province' => $validated['juri_province'],
                    'city' => $validated['juri_city'],
                    'barangay' => $validated['juri_barangay'],
                    'subdivision' => $validated['juri_subdivision'],
                    'street' => $validated['juri_street'],
                    'upblb_num' => $validated['juri_upblb_num'],
                    'zip' => $validated['juri_zip'],
                ]);

                Address::create([
                    'id' => $emp_address_id,
                    'region' => $validated['emp_region'],
                    'province' => $validated['emp_province'],
                    'city' => $validated['emp_city'],
                    'barangay' => $validated['emp_barangay'],
                    'subdivision' => $validated['emp_subdivision'],
                    'street' => $validated['emp_street'],
                    'upblb_num' => $validated['emp_upblb_num'],
                    'zip' => $validated['emp_zip'],
                ]);

                Employer::create([
                    'id' => $emp_id,
                    'address_id' => $emp_address_id,
                    'entity_no' => $validated['emp_entity_no'],
                    'full_name' => $validated['emp_full_name'],
                    'gender' => $validated['emp_gender'],
                    'birth_date' => $validated['emp_birth_date'],
                    'contact_no' => $validated['emp_contact_no'],
                    'email' => $validated['emp_email'],
                    'special_category' => $validated['emp_special_category'],
                ]);

                Juridical::create([
                    'id' => $juri_id,
                    'employer_id' => $emp_id,
                    'address_id' => $juri_address_id,
                    'entity_no' => $validated['juri_entity_no'],
                    'name' => $validated['juri_name'],
                    'date_reg' => $validated['juri_date_reg'],
                    'contact_no' => $validated['juri_contact_no'],
                    'contact_email' => $validated['juri_contact_email'],
                    'line_of_industry' => $validated['juri_line_of_industry'],
                    'capitalization' => $validated['juri_capitalization'],
                ]);
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Business created successfully.',
        ], 201);

    }

    public function updateEmployer(Employer $employer, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gender' => 'required|string',
            'contact_no' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'special_category' => 'required|string|max:255',
        ]);

        $employer->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Employer updated successfully.',
        ]);
    }

    public function updateJuridical(Juridical $juridical, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_no' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:150',
            'line_of_industry' => 'required|string',
            'capitalization' => 'numeric',
        ]);

        $juridical->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Business updated successfully',
        ]);
    }

    public function updateAddress(Address $address, Request $request): JsonResponse
    {

        $validated = $request->validate([
            'region' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'barangay' => 'nullable|string|max:100',
            'subdivision' => 'nullable|string|max:150',
            'street' => 'nullable|string|max:150',
            'upblb_num' => 'nullable|string|max:50',
            'zip' => 'nullable|string|max:15',
        ]);

        $address->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Address updated successfuly',
        ]);
    }

    public function renew(Juridical $juridical): JsonResponse
    {
        $juridical->update([
            'registration_type' => 'RENEWAL',
        ]);

        return response()
            ->json([
                'status' => 'success',
                'message' => 'Business renewed successfully.',
            ], 200);
    }

    public function changeStatus(Request $request, Juridical $juridical): JsonResponse
    {

        $validated = $request->validate([
            'bus_status' => ['required', 'string', 'in:ACTIVE,INACTIVE'],
        ]);

        $juridical->update($validated);

        return response()
            ->json([
                'status' => 'success',
                'message' => 'Business status changed successfully.',
            ]);
    }

    public function setLocation(Request $request, Address $address) {
        $validated = $request->validate([
            'longitude' => 'required|decimal:0,7',
            'latitude' => 'required|decimal:0,7',
        ]);

        $address->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Location saved.',
        ]);
    }
}
