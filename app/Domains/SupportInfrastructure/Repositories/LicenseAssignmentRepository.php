<?php
namespace App\Domains\SupportInfrastructure\Repositories;

use IncadevUns\CoreDomain\Models\LicenseAssignment;

class LicenseAssignmentRepository {
    protected $model;

    public function __construct(LicenseAssignment $model){
        $this->model = $model;
    }

    public function all(){
        return $this->model->all();
    }

    public function find(int $id){
        return $this->model->find($id);
    }

    public function create(array $data){
        return $this->model->create($data);
    }

    public function update(int $id, array $data){
        $assignment = $this->model->find($id);
        if ($assignment){
            $assignment->update($data);
            return $assignment;
        }
        return null;
    }

    public function delete(int $id){
        $assignment = $this->model->find($id);
        if($assignment){
            return $assignment->delete();
        }
        return false;
    }
}
