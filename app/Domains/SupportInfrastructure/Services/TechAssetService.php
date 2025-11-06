<?php

namespace App\Domains\SupportInfrastructure\Services;
use App\Domains\SupportInfrastructure\Repositories\TechAssetRepository;

class TechAssetService{
    protected $repository;

    public function __construct(TechAssetRepository $repository){
        $this->repository = $repository;
    }

    public function getAllAssets(){
        return $this->repository->all();
    }

    public function getAssetById(int $id){
        return $this->repository->find($id);
    }

    public function createAsset(array $data){
        return $this->repository->create($data);
    }

    public function updateAsset(int $id, array $data){
        return $this->repository->update($id, $data);
    }

    public function deleteAsset(int $id){
        return $this->repository->delete($id);
    }
}