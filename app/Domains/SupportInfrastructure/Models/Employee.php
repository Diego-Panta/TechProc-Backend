<?php

namespace App\Domains\SupportInfrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\AuthenticationSessions\Models\User;
use App\Domains\Administrator\Models\Position;

class Employee extends Model{

    protected $table = 'employees';
    public $timestamps = FALSE;

    protected $fillable = ['id', 'employee_id','hire_date','position_id','department_id','user_id','employment_status','schedule','speciality','salary','created_at'];

    // Relación con User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con Position
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    // Relación con Department
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    #public function softwares(){
        #return $this->hasMany(Software::class);
    #}

}