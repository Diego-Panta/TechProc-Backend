<?php

namespace App\Domains\SupportInfrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\SupportInfrastructure\Services\LicenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
#no está usando los servicios
class LicenseController extends Controller{
    protected $service;

    public function __construct(LicenseService $service) {
        $this->service = $service;
    }
    public function index(){
        return response()->json($this->service->getAllLicenses());
    }

    public function store(Request $request){
        $data = $request->validate([
            'software_id' => 'required|integer|exists:softwares,id',
            'key_code' => 'nullable|string',
            'provider' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'cost' => 'nullable|numeric',
            'status' => 'required|string'
            ]);

        // Convertir fechas del formato ISO 8601 a formato MySQL
        if (isset($data['purchase_date'])) {
            $data['purchase_date'] = date('Y-m-d', strtotime($data['purchase_date']));
        }
        if (isset($data['expiration_date'])) {
            $data['expiration_date'] = date('Y-m-d', strtotime($data['expiration_date']));
        }

        $license = $this->service->createLicense($data);
        return response()->json($license, 201);
    }
    public function show($id){
        try{
            $license = $this->service->getLicenseById($id);
            return response()->json($license);
        } catch(ModelNotFoundException $e){
            return response()->json(['message' => 'License no encontrada'], 404);
        }
    }

    #no me funciona este método update, no actualiza nada
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'software_id' => 'sometimes|integer|exists:softwares,id',
            'key_code' => 'nullable|string',
            'provider' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'cost' => 'nullable|numeric',
            'status' => 'nullable|string'
        ]);

        // Convertir fechas del formato ISO 8601 a formato MySQL
        if (isset($data['purchase_date'])) {
            $data['purchase_date'] = date('Y-m-d', strtotime($data['purchase_date']));
        }
        if (isset($data['expiration_date'])) {
            $data['expiration_date'] = date('Y-m-d', strtotime($data['expiration_date']));
        }

        try{
            $license = $this->service->updateLicense($id, $data);
            return response()->json($license);
        } catch(ModelNotFoundException $e){
            return response()->json(['message' => 'Licencia no encontrada'], 404);
        }   
    }

    public function destroy($id){
        try{
            $this->service->deleteLicense($id);
            return response()->noContent();
        }catch(ModelNotFoundException $e){
            return response()->json(['message' => 'Licencia no encontrada'], 404);
        }
    }
}