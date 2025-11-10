<?php
namespace App\Domains\SupportInfrastructure\Repositories;

use App\Domains\SupportInfrastructure\Models\Hardware;

class HardwareRepository {
    protected $model;

    public function __construct(Hardware $model){
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
        $hardware = $this->model->find($id);
        if ($hardware){
            $hardware->update($data);
            return $hardware;
        }
        return null;
    }

    public function delete(int $id){
        $hardware = $this->model->find($id);
        if($hardware){
            return $hardware->delete();
        }
        return false;
    }
}
