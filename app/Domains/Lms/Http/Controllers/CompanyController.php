<?php

namespace App\Domains\Lms\Http\Controllers;

use App\Domains\Lms\Services\CompanyService;
use App\Domains\Lms\Http\Requests\CreateCompanyRequest;
use App\Domains\Lms\Http\Requests\UpdateCompanyRequest;
use App\Domains\Lms\Resources\CompanyCollection;
use App\Domains\Lms\Resources\CompanyResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('limit', 20);

        $filters = [
            'search' => $request->input('search'),
            'industry' => $request->input('industry'),
        ];

        $filters = array_filter($filters, fn($value) => !is_null($value));
        $companies = $this->companyService->getAllCompanies($filters, $perPage);

        return response()->json(['success' => true, 'data' => new CompanyCollection($companies)]);
    }

    public function show(int $companyId): JsonResponse
    {
        $company = $this->companyService->getCompanyById($companyId);

        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => new CompanyResource($company)]);
    }

    public function store(CreateCompanyRequest $request): JsonResponse
    {
        $company = $this->companyService->createCompany($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Empresa creada exitosamente',
            'data' => ['id' => $company->id],
        ], 201);
    }

    public function update(UpdateCompanyRequest $request, int $companyId): JsonResponse
    {
        $company = $this->companyService->updateCompany($companyId, $request->validated());

        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Empresa actualizada exitosamente']);
    }

    public function destroy(int $companyId): JsonResponse
    {
        $deleted = $this->companyService->deleteCompany($companyId);

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Empresa no encontrada'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Empresa eliminada exitosamente']);
    }
}
