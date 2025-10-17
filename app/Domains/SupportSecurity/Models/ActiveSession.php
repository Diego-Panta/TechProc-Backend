<?php
namespace App\Domains\SupportSecurity\Models;

use Illuminate\Database\Eloquent\Model;
#use App\Models\User;

class ActiveSession extends Model
{
    #protected $table = 'softwares';
    public $timestamps = FALSE;
    protected $fillable = ['id','session_id','user_id','ip_address','device','start_date','active','blocked'];

    #dónde está USER?
    #public function user(){
       # return $this->hasMany(User::class);
    #}

    #Registrar los intentos de acceso
    public function securityLogs(){
        return $this->hasMany(SecurityLog::class, 'session_id')
    }



}