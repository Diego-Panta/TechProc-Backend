<?php
namespace App\Domains\SupportSecurity\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityAlert extends Model
{
    #aquí debería ver las relaciones foráneas
    #protected $table = 'softwares';
    public $timestamps = FALSE;
    protected $fillable = ['id','id_security_alert','threat_type','severity','status','blocked_ip_id','detection_date'];

    public function blockedIp(){
        return $this->belongsTo(BlockedIp::class,'blocked_ip_id');
    }

    public function incidents(){
        return $this->hasMany(Incident::class,'alert_id');
    }



}