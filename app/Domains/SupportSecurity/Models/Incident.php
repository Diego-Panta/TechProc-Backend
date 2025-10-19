<?php
namespace App\Domains\SupportSecurity\Models;

use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    #protected $table = 'softwares';
    public $timestamps = FALSE;
    protected $fillable = ['id','id_incident','alert_id','responsible_id','title','status','report_date'];

    public function alert(){
        return $this->belongsTo(SecurityAlert::class, 'alert_id');
    }

    #public function responsible(){
        #return $this->belongsTo(User::class, 'responsible_id');
    #}



}