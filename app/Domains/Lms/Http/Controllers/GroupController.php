<?php

namespace App\Domains\Lms\Http\Controllers;

use App\Domains\Lms\Services\GroupService;
use App\Domains\Lms\Http\Requests\CreateGroupRequest;
use App\Domains\Lms\Http\Requests\UpdateGroupRequest;
use App\Domains\Lms\Resources\GroupCollection;
use App\Domains\Lms\Resources\GroupResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    protected GroupService $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    /**
     * Display a listing of groups
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('limit', 20);

        $filters = [
            'course_id' => $request->input('course_id'),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'start_date_from' => $request->input('start_date_from'),
            'start_date_to' => $request->input('start_date_to'),
        ];

        $filters = array_filter($filters, fn($value) => !is_null($value));
        $groups = $this->groupService->getAllGroups($filters, $perPage);

        return response()->json(['success' => true, 'data' => new GroupCollection($groups)]);
    }

    /**
     * Display the specified group
     */
    public function show(int $groupId): JsonResponse
    {
        $group = $this->groupService->getGroupById($groupId);

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Grupo no encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => new GroupResource($group)]);
    }

    /**
     * Store a newly created group
     */
    public function store(CreateGroupRequest $request): JsonResponse
    {
        $group = $this->groupService->createGroup($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Grupo creado exitosamente',
            'data' => ['id' => $group->id],
        ], 201);
    }

    /**
     * Update the specified group
     */
    public function update(UpdateGroupRequest $request, int $groupId): JsonResponse
    {
        $group = $this->groupService->updateGroup($groupId, $request->validated());

        if (!$group) {
            return response()->json(['success' => false, 'message' => 'Grupo no encontrado'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Grupo actualizado exitosamente']);
    }

    /**
     * Remove the specified group
     */
    public function destroy(int $groupId): JsonResponse
    {
        $deleted = $this->groupService->deleteGroup($groupId);

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Grupo no encontrado'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Grupo eliminado exitosamente']);
    }
}
