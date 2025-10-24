<?php

namespace App\Domains\SupportInfrastructure\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\SupportInfrastructure\Models\Employee;
use Illuminate\Http\Request;


class EmployeeController extends Controller{
    public function index(){
        return response()->json(Employee::all());
    }

    public function store(Request $request){
        $data = $request->validate([
                'id' => 'sometimes|integer', 
                'employee_id'=> 'nullable|integer',
                'hire_date'=>'nullable|date',
                'position_id'=>'nullable|integer',
                'department_id'=>'nullable|integer',
                'user_id'=>'nullable|integer',
                'employment_status'=>'nullable|string',
                'schedule'=>'nullable|string',
                'speciality'=>'nullable|string',
                'salary'=>'nullable|integer',
                'created_at'=>'nullable|date',
            ]);

        $employee = Employee::create($data);
        return response()->json($employee, 201);
    }
        public function show($id){
            return response()->json(
                Employee::with(['softwares'])->findOrFail($id)
            );
        }

        public function update(Request $request, $id)
        {
            $employee = Employee::findOrFail($id);
            $employee->update($request->all());
            return response()->json($employee);
        }

        public function destroy($id){
            Employee::findOrFail($id)->delete();
            return response()->json(null, 204);
        }
    }




