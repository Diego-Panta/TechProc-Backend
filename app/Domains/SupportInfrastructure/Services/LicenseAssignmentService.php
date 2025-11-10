<?php

namespace App\Domains\SupportInfrastructure\Services;
use App\Domains\SupportInfrastructure\Repositories\LicenseAssignmentRepository;

class LicenseAssignmentService{
    protected $repository;

    public function __construct(LicenseAssignmentRepository $repository){
        $this->repository = $repository;
    }

    public function getAllAssignment(){
        return $this->repository->all();
    }

    public function getAssignmentById(int $id){
        return $this->repository->find($id);
    }

    public function createAssignment(array $data){
        return $this->repository->create($data);
    }

    public function updateAssignment(int $id, array $data){
        return $this->repository->update($id, $data);
    }

    public function deleteAssignment(int $id){
        return $this->repository->delete($id);
    }
}