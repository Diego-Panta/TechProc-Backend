<?php

namespace App\Domains\SupportInfrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\SupportInfrastructure\Services\SoftwareService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SoftwareController extends Controller{
    protected $service;

    public function __construct(SoftwareService $service) {
        $this->service = $service;
    }

    public function index(){
        return response()->json($this->service->getAllSoftwares());
    }

    public function store(Request $request){
        $data = $request->validate([
            'asset_id' =>'required|integer',
            'software_name' =>'required|string',
            'version' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        // Convertir fechas del formato ISO 8601 a formato MySQL
        if (isset($data['installation_date'])) {
            $data['installation_date'] = date('Y-m-d', strtotime($data['installation_date']));
        }
        if (isset($data['last_update'])) {
            $data['last_update'] = date('Y-m-d', strtotime($data['last_update']));
        }

        $software = $this->service->createSoftware($data);
        return response()->json($software, 201);
    }

        public function show($id){
            $software = $this->service->getSoftwareById($id);
            if (!$software) {
                return response()->json(['message' => 'Software no encontrado'], 404);
            }
            return response()->json($software);
        }

        public function update(Request $request, $id)
        {
            $data = $request->validate([
                'asset_id' =>'sometimes|integer|exists:tech_assets,id',
                'software_name' =>'nullable|string',
                'version' => 'nullable|string',
                'type' => 'nullable|string',
            ]);

            // Convertir fechas del formato ISO 8601 a formato MySQL
            if (isset($data['installation_date'])) {
                $data['installation_date'] = date('Y-m-d', strtotime($data['installation_date']));
            }
            if (isset($data['last_update'])) {
                $data['last_update'] = date('Y-m-d', strtotime($data['last_update']));
            }

            try{
                $software = $this->service->updateSoftware($id, $data);
                return response()->json($software);
            }catch(ModelNotFoundException $e){
                return response()->json(['message' => 'Software no encontrado'], 404);
            }
            $software->update($data);        }

        public function destroy($id){
            try{
                $this->service->deleteSoftware($id);
                return response()->noContent();
            } catch(ModelNotFoundException $e){
                return response()->json(['message' => 'Software no encontrado'], 404);
            }
        }
    }



