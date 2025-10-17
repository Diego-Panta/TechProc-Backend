<?php
namespace App\Domains\SupportInfrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    public $timestamps = FALSE;
    protected $fillable = ['id','id_blocked_ip','ip_address','reason','block_date','active'];



}