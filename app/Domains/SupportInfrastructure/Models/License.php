<?php

namespace App\Domains\SupportInfrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model{
    protected $fillable = ['software_name','license_key','license_type','provider','purchase_date',
    'expiration_date','seats_total','seats_used','cost_annual','status','responsible_id','notes',];

    
    public $timestamps = FALSE;
    public function software(){
        return $this->belongsTo(Software::class, 'software_id');
    }

    public function responsible(){
        return $this->belongsTo(Employee::class, 'responsible_id');
    }

}