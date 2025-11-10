<?php

namespace App\Domains\SupportInfrastructure\Services;
use App\Domains\SupportInfrastructure\Repositories\LicenseRepository;

class LicenseService{
    protected $repository;

    public function __construct(LicenseRepository $repository){
        $this->repository = $repository;
    }

    public function getAllLicenses(){
        return $this->repository->all();
    }

    public function getLicenseById(int $id){
        return $this->repository->find($id);
    }

    public function createLicense(array $data){
        return $this->repository->create($data);
    }

    public function updateLicense(int $id, array $data){
        return $this->repository->update($id, $data);
    }

    public function deleteLicense(int $id){
        return $this->repository->delete($id);
    }
}