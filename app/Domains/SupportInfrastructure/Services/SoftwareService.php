<?php

namespace App\Domains\SupportInfrastructure\Services;
use App\Domains\SupportInfrastructure\Repositories\SoftwareRepository;

class SoftwareService{
    protected $repository;

    public function __construct(SoftwareRepository $repository){
        $this->repository = $repository;
    }

    public function getAllSoftwares(){
        return $this->repository->all();
    }

    public function getSoftwareById(int $id){
        return $this->repository->find($id);
    }

    public function createSoftware(array $data){
        return $this->repository->create($data);
    }

    public function updateSoftware(int $id, array $data){
        return $this->repository->update($id, $data);
    }

    public function deleteSoftware(int $id){
        return $this->repository->delete($id);
    }
}