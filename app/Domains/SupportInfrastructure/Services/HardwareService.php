<?php

namespace App\Domains\SupportInfrastructure\Services;
use App\Domains\SupportInfrastructure\Repositories\HardwareRepository;

class HardwareService{
    protected $repository;

    public function __construct(HardwareRepository $repository){
        $this->repository = $repository;
    }

    public function getAllHardwares(){
        return $this->repository->all();
    }

    public function getHardwareById(int $id){
        return $this->repository->find($id);
    }

    public function createHardware(array $data){
        return $this->repository->create($data);
    }

    public function updateHardware(int $id, array $data){
        return $this->repository->update($id, $data);
    }

    public function deleteHardware(int $id){
        return $this->repository->delete($id);
    }
}