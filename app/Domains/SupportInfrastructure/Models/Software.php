<?php
# namespace IncadevUns\CoreDomain\Models;
namespace App\Domains\SupportInfrastructure\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Software extends Model
{
    use HasFactory;
    protected $table = 'softwares';
    protected $fillable = [
        'asset_id',
        'name',
        'version',
        'type'];

    public function licenses(): HasMany{
        return $this->hasMany(License::class, 'software_id');
    }

    public function asset(): BelongsTo{
        return $this->belongsTo(TechAsset::class, 'asset_id');
    }



}