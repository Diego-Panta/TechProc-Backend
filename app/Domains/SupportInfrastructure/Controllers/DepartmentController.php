<?php

namespace App\Domains\SupportInfrastructure\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\SupportInfrastructure\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index()
    {
        return response()->json(Department::all());
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'department_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'created_at' => 'nullable|date'
        ]);

        $department = Department::create($data);
        return response()->json($department, 201);
    }

    /**
     * Display the specified department.
     */
    public function show($id)
    {
        return response()->json(
            Department::with(['employees.user', 'employees.position'])->findOrFail($id)
        );
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $data = $request->validate([
            'department_name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'updated_at' => 'nullable|date'
        ]);

        $department->update($data);
        return response()->json($department);
    }

    /**
     * Remove the specified department.
     */
    public function destroy($id)
    {
        Department::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
