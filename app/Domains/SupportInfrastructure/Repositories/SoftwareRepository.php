<?php
namespace App\Domains\SupportInfrastructure\Repositories;

use App\Domains\SupportInfrastructure\Models\Software;

class SoftwareRepository {
    protected $model;

    public function __construct(Software $model){
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
        $software = $this->model->find($id);
        if ($software){
            $software->update($data);
            return $software;
        }
        return null;
    }

    public function delete(int $id){
        $software = $this->model->find($id);
        if($software){
            return $software->delete();
        }
        return false;
    }
}
