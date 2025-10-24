<?php

namespace App\Domains\SupportSecurity\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Administrator\Models\User;

class SecurityLog extends Model
{
    #protected $table = 'softwares';
    public $timestamps = FALSE;
    protected $fillable = ['id', 'id_security_logs', 'user_id', 'event_type', 'description', 'source_ip', 'event_date'];

    public function session()
    {
        return $this->belongsTo(ActiveSession::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
