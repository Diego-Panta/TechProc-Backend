<?php

namespace App\Domains\SupportInfrastructure\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\SupportInfrastructure\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LicenseController extends Controller{
    public function index(){
        return response()->json(License::with(['software','responsible'])->get());
    }

    public function store(Request $request){
        $data = $request->validate([
            'id' => 'sometimes|integer',
            'id_license' => 'nullable|integer',
            'software_name' => 'required|string',
            'license_key' => 'required|string',
            'license_type'=> 'nullable|string',
            'provider'=> 'nullable|string',
            'purchase_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'seats_total' => 'nullable|integer',
            'seats_used' =>'nullable|integer',
            'cost_annual' =>'nullable|numeric',
            'status' =>'nullable|string',
        #'responsible_id' =>'nullable|exists:employees,id',
            'responsible_id' => 'nullable|integer',
            'notes' =>'nullable|string',
            'created_at' =>'nullable|date',
            ]);

        $license = License::create($data);
        return response()->json($license, 201);
    }
        public function show($id){
            return response()->json(
                License::with(['software', 'responsible'])->findOrFail($id)
            );
        }

        #no me funciona este método update, no actualiza nada
        public function update(Request $request, $id)
        {
            Log::info('Update request:', $request->all());
            $license = License::findOrFail($id);
            
            $data = $request->validate([
            'software_name' => 'sometimes|string',
            'license_key' => 'sometimes|string',
            'license_type'=> 'nullable|string',
            'provider'=> 'nullable|string',
            'purchase_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'seats_total' => 'nullable|integer',
            'seats_used' =>'nullable|integer',
            'cost_annual' =>'nullable|numeric',
            'status' =>'nullable|string',
        #'responsible_id' =>'nullable|exists:employees,id',
            'responsible_id' => 'nullable|integer',
            'notes' =>'nullable|string'
            ]);
            $license->update($data);
            return response()->json($license);
            Log::info('Updated license:', $license->toArray());
        }

        public function destroy($id){
            License::findOrFail($id)->delete();
            return response()->json(null, 204);
        }
}