<?php

namespace App\Domains\SupportInfrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\SupportInfrastructure\Services\HardwareService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class HardwareController extends Controller{
    protected $service;

    public function __construct(HardwareService $service) {
        $this->service = $service;
    }
    public function index(){
        $hardware = $this->service->getAllHardwares();
        return response()->json($hardware);

    }

    public function store(Request $request){
        $data = $request->validate([
            'asset_id' =>'required|integer|exists:tech_assets,id',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'warranty_expiration' => 'nullable|date',
            'specs' => 'nullable|string'
        ]);

        // Convertir fechas del formato ISO 8601 a formato MySQL
        if (isset($data['warranty_expiration'])) {
            $data['warranty_expiration'] = date('Y-m-d', strtotime($data['warranty_expiration']));
        }

        $hardware = $this->service->createHardware($data);
        return response()->json($hardware, 201);
    }

    public function show($id){
            $hardware = $this->service->getHardwareById($id);
            if (!hardware) {
                return response()->json(['message' => 'Hardware no encontrado'], 404);
            }
            return response()->json($hardware);
        }

        public function update(Request $request, $id)
        {
            $data = $request->validate([
                'asset_id' =>'sometimes|integer|exists:tech_assets,id',
                'model' => 'nullable|string',
                'serial_number' => 'nullable|string',
                'warranty_expiration' => 'nullable|date',
                'specs' => 'nullable|string'
            ]);

            // Convertir fechas del formato ISO 8601 a formato MySQL
            if (isset($data['warranty_expiration'])) {
                $data['warranty_expiration'] = date('Y-m-d', strtotime($data['warranty_expiration']));
            }
            try{
                $hardware = $this->service->updateHardware($id, $data);
                return response()->json($hardware);
            } catch(ModelNotFoundException $e){
                return response()->json(['message' => 'Software no encontrado'], 404);
            }

        }

    public function destroy($id){
        $deleted = $this->service->deleteHardware($id);
        if(!$deleted){
            return response()->json(['message' => 'Hardware no encontrado'], 404);
        }
        return response()->json(['message' => 'Hardware eliminado correctamente']);
    }
}




