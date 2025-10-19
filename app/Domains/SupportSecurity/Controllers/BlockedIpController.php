<?php

namespace App\Domains\SupportSecurity\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\SupportSecurity\Models\BlockedIp;
use Illuminate\Http\Request;


class BlockedIpController extends Controller{
    public function index(){
        return response()->json(BlockedIp::all());
    }

    public function store(Request $request){
        $data = $request->validate([
            'id' => 'sometimes|integer',
            'id_blocked_ip' => 'nullable|integer',
            'ip_address' => 'required|string',
            'reason' => 'nullable|string',
            'block_date' => 'nullable|date',
            'active' => 'nullable|boolean',
        ]);


        $blockedIp = BlockedIp::create($data);
        return response()->json($blockedIp, 201);
    }
    public function show($id){
        return response()->json(
            BlockedIp::with(['softwares'])->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $blockedIp = BlockedIp::findOrFail($id);
        $blockedIp->update($request->all());
        return response()->json($blockedIp);
    }

    public function destroy($id){
        BlockedIp::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}