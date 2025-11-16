<?php
namespace App\Domains\SupportInfrastructure\Repositories;

use IncadevUns\CoreDomain\Models\TechAsset;

class TechAssetRepository {
    protected $model;

    public function __construct(TechAsset $model){
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
        $asset = $this->model->find($id);
        if ($asset){
            $asset->update($data);
            return $asset;
        }
        return null;
    }

    public function delete(int $id){
        $asset = $this->model->find($id);
        if($asset){
            return $asset->delete();
        }
        return false;
    }
}
