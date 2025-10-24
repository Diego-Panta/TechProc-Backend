<?php
namespace App\Domains\SupportSecurity\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityConfiguration extends Model
{
    protected $table = 'security_configurations';
    public $timestamps = FALSE;
    protected $fillable = ['id','id_security_configuration','user_id','modulo','parameter','value','active','created_at'];


    #public function user(){
        #return $this->belongsTo(User::class, 'responsible_id');
    #}



}