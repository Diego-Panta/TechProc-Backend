<?php
namespace App\Domains\SupportInfrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    protected $table = 'softwares';
    public $timestamps = FALSE;
    protected $fillable = ['id','id_software','software_name','version','category','vendor',
    'license_id','installation_date','last_update','created_at'];

    public function licenses(){
        return $this->hasMany(License::class);
    }



}