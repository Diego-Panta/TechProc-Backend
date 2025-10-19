<?php

namespace App\Domains\SupportSecurity\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\SupportSecurity\Models\SecurityLog;
use Illuminate\Http\Request;


class SecurityLogController extends Controller{
    public function index(){
        return response()->json(SecurityLog::with(['session'])->get());
    }

    public function store(Request $request){
            $data = $request->validate([
                'id' => 'sometimes|integer',
                'id_security_logs' => 'nullable|integer',
                'user_id'=> 'nullable|integer',
                'event_type' => 'nullable|string',
                'description' => 'nullable|string',
                'source_ip' => 'nullable|integer',
                'event_date' => 'nullable|date',
            ]);

        $securityLog = SecurityLog::create($data);
        return response()->json($incident, 201);
    }
    public function show($id){
        return response()->json(
            SecurityLog::with(['session'])->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $securityLog = SecurityLog::findOrFail($id);
        $securityLog->update($request->all());
        return response()->json($securityLog);
    }

    public function destroy($id){
        SecurityLog::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}




