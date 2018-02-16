<?php
namespace Parking\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FreeSpotClaim
 *
 * @package Parking\Models
 */
class FreeSpotClaim extends Model
{
    protected $table = 'free_spots_claims';

    protected $fillable = [
        'claimer_user',
        'date_claimed',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function freeSpot()
    {
        return $this->belongsTo(FreeSpot::class);
    }
}