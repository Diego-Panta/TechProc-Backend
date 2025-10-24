<?php

namespace App\Domains\SupportSecurity\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\SupportSecurity\Models\ActiveSession;
use Illuminate\Http\Request;


class ActiveSessionController extends Controller{
    public function index(){
        return response()->json(ActiveSession::with(['securityLogs'])->get());
    }

    public function store(Request $request){
            $data = $request->validate([
                'id' => 'sometimes|integer',
                'session_id'=> 'nullable|integer' ,
                'user_id' => 'nullable|integer',
                'ip_address'=> 'nullable|string',
                'device '=> 'nullable|string',
                'start_date' => 'nullable|string',
                'active' => 'nullable|string',
                'blocked' => 'nullable|string',
            ]);
        $activeSession = ActiveSession::create($data);
        return response()->json($activeSession, 201);
    }
    public function show($id){
        return response()->json(
            ActiveSession::with(['securityLogs'])->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $activeSession = ActiveSession::findOrFail($id);
        $activeSession->update($request->all());
        return response()->json($activeSession);
    }

    public function destroy($id){
        ActiveSession::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}




