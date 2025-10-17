<?php

namespace App\Domains\SupportInfrastructure\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\SupportInfrastructure\Models\Software;
use Illuminate\Http\Request;


class SoftwareController extends Controller{
    public function index(){
        return response()->json(Software::all());
    }

    public function store(Request $request){
        $data = $request->validate([ #qué te parece?
            'id' => 'sometimes|integer',
            'id_software' =>'nullable|integer',
            'software_name' =>'required|string',
            'version' => 'nullable|string',
            'category' => 'nullable|string',
            'vendor' => 'nullable|string',
            'license_id' => 'nullable|exists:licenses,id',
            'installation_date' => 'nullable|date',
            'last_update'=> 'nullable|date',
            'created_at' => 'nullable|date',
        ]);

        $software = Software::create($data);
        return response()->json($software, 201);
    }

        public function show($id){
            return response()->json(
                Software::with(['employee', 'licenses'])->findOrFail($id)
            );
        }

        public function update(Request $request, $id)
        {
            $software = Software::findOrFail($id);
            $software->update($request->all());
            return response()->json($software);
        }

        public function destroy($id){
            Software::findOrFail($id)->delete();
            return response()->json(null, 204);
        }
    }



