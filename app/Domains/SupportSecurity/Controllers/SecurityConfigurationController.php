<?php

namespace App\Domains\SupportSecurity\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\SupportSecurity\Models\SecurityConfiguration;
use Illuminate\Http\Request;


class SecurityConfigurationController extends Controller{
    public function index(){
        return response()->json(SecurityConfiguration::all());
    }

    public function store(Request $request){
        $data = $request->validate([
            'id' => 'sometimes|integer',
            'id_security_configuration' => 'nullable|integer',
            'user_id'=> 'nullable|integer',
            'modulo' => 'nullable|string',
            'parameter' => 'nullable|string',
            'value' => 'nullable|string',
            'active'=> 'nullable|string',
            'created_at' => 'nullable|date',
        ]);


        $securityConfiguration = SecurityConfiguration::create($data);
        return response()->json($securityConfiguration, 201);
    }
    public function show($id){
        return response()->json(
            SecurityConfiguration::with(['alert'])->findOrFail($id)
        );
    }

    public function update(Request $request, $id){
        $securityConfiguration = SecurityConfiguration::findOrFail($id);
        $securityConfiguration->update($request->all());
        return response()->json($securityConfiguration);
    }

    public function destroy($id){
        SecurityConfiguration::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}




