<?php

namespace App\Domains\SupportInfrastructure\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Domains\SupportInfrastructure\Services\TechAssetService;

class TechAssetController extends Controller{
    protected $service;

    public function __construct(TechAssetService $service){
        $this->service = $service;
    }

    public function index(){
        $assets = $this->service->getAllAssets();
        return response()->json($assets);
    }

    public function show($id){
        $asset = $this->service->getAssetById($id);
        if (!$asset){
            return response()->json(['message' => 'Activo no encontrado'], 404);
        }
        return response()->json($asset);
    }

    public function store(Request $request){
        $data = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'status' => 'nullable|string',
            'acquisition_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if(isset($data['acquisition_date'])){
            $data['acquisition_date'] = date('Y-m-d', strtotime($data['acquisition_date']));
        }
        if(isset($data['expiration_date'])){
            $data['expiration_date'] = date('Y-m-d', strtotime($data['expiration_date']));
        }

        $asset = $this->service->createAsset($data);
        return response()->json($asset, 201);
    }

    public function update(Request $request, $id){
        $data = $request->validate([
            'name' => 'sometimes|string',
            'type' => 'sometimes|string',
            'status' => 'nullable|string',
            'acquisition_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'user_id' => 'sometimes|integer|exists:users,id',
        ]);

        if(isset($data['acquisition_date'])){
            $data['acquisition_date'] = date('Y-m-d', strtotime($data['acquisition_date']));
        }
        if(isset($data['expiration_date'])){
            $data['expiration_date'] = date('Y-m-d', strtotime($data['expiration_date']));
        }
        $asset = $this->service->updateAsset($id, $data);

        if(!$asset){
            return response()->json(['message' => 'Activo no encontrado'], 404);
        }   

        return response()->json($asset);
    }

    public function destroy($id){
        $deleted = $this->service->deleteAsset($id);
        if(!$deleted){
            return response()->json(['message' => 'Activo no encontrado'], 404);
        }
        return response()->json(['message' => 'Activo eliminado correctamente']);
    }

}