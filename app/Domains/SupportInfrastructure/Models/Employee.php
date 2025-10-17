<?php

namespace App\Domains\SupportInfrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model{

    protected $table = 'employees';
    public $timestamps = FALSE;

    protected $fillable = ['id', 'employee_id','hire_date','position_id','department_id','user_id','employment_status','schedule','speciality','salary','created_at'];

    #public function softwares(){
        #return $this->hasMany(Software::class);
    #}

}