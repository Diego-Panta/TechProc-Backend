<?php

namespace App\Domains\SupportSecurity\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\SupportSecurity\Models\Incident;
use Illuminate\Http\Request;


class IncidentController extends Controller{
    public function index(){
        return response()->json(Incident::with(['alert'])->get());
    }

    public function store(Request $request){
            $data = $request->validate([
                'id' => 'sometimes|integer',
                'id_incident' => 'nullable|integer',
                'alert_id'=> 'nullable|integer',
                'responsible_id' => 'nullable|integer',
                'title' => 'nullable|string',
                'status' => 'nullable|string',
                'report_date' => 'nullable|date',
            ]);

        $incident = Incident::create($data);
        return response()->json($incident, 201);
    }
    public function show($id){
        return response()->json(
            Incident::with(['alert'])->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $incident = Incident::findOrFail($id);
        $incident->update($request->all());
        return response()->json($incident);
    }

    public function destroy($id){
        Incident::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}




