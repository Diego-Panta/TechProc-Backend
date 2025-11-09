<?php
namespace App\Domains\SupportInfrastructure\Repositories;

use App\Domains\SupportInfrastructure\Models\License;

class LicenseRepository {
    protected $model;

    public function __construct(License $model){
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
        $license = $this->model->find($id);
        if ($license){
            $license->update($data);
            return $license;
        }
        return null;
    }

    public function delete(int $id){
        $license = $this->model->find($id);
        if($license){
            return $license->delete();
        }
        return false;
    }
}
