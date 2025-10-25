<?php

namespace App\Domains\Administrator\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Administrator\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * Display a listing of positions.
     * Filters: ?department_id=3&position_name=Developer
     */
    public function index(Request $request)
    {
        $query = Position::with(['department']);

        // Filtrar por department_id
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filtrar por position_name (búsqueda parcial)
        if ($request->has('position_name')) {
            $query->where('position_name', 'ILIKE', '%' . $request->position_name . '%');
        }

        // Filtrar por ID específico
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }

        return response()->json($query->get());
    }

    /**
     * Store a newly created position.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'position_name' => 'required|string|max:100',
            'department_id' => 'required|integer|exists:departments,id',
            'created_at' => 'nullable|date'
        ]);

        $position = Position::create($data);
        return response()->json($position, 201);
    }

    /**
     * Display the specified position.
     */
    public function show($id)
    {
        return response()->json(
            Position::with(['department', 'employees.user'])->findOrFail($id)
        );
    }

    /**
     * Update the specified position.
     */
    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);

        $data = $request->validate([
            'position_name' => 'sometimes|string|max:100',
            'department_id' => 'sometimes|integer|exists:departments,id',
            'updated_at' => 'nullable|date'
        ]);

        $position->update($data);
        return response()->json($position);
    }

    /**
     * Remove the specified position.
     */
    public function destroy($id)
    {
        Position::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
