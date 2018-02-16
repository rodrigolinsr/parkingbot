<?php
namespace Parking\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FreeSpot
 *
 * @package Parking\Models
 */
class FreeSpot extends Model
{
    protected $table = 'free_spots';

    protected $fillable = [
        'date_from',
        'date_to',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function claims()
    {
        return $this->hasMany(FreeSpotClaim::class, 'free_spot_id', 'id');
    }
}