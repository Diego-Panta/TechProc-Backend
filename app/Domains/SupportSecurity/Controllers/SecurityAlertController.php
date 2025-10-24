<?php

namespace App\Domains\SupportSecurity\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\SupportSecurity\Models\SecurityAlert;
use Illuminate\Http\Request;


class SecurityAlertController extends Controller{
    public function index(){
        return response()->json(SecurityAlert::with(['blockedIp','incidents'])->get());
    }

    public function store(Request $request){
            $data = $request->validate([
                'id' => 'sometimes|integer',
                'id_security_alert' => 'nullable|integer',
                'threat_type'=> 'nullable|string',
                'severity' => 'nullable|string',
                'status' => 'nullable|string',
                'blocked_ip_id' => 'nullable|integer',
                'detection_date' => 'nullable|date',
            ]);
        $securityAlert = SecurityAlert::create($data);
        return response()->json($securityAlert, 201);
    }
    public function show($id){
        return response()->json(
            SecurityAlert::with(['blockedIp','incidents'])->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $securityAlert = SecurityAlert::findOrFail($id);
        $securityAlert->update($request->all());
        return response()->json($securityAlert);
    }

    public function destroy($id){
        SecurityAlert::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}




