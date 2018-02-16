<?php
namespace Parking\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Spot
 *
 * @package Parking\Models
 */
class Spot extends Model
{
    protected $table = 'spots';

    protected $fillable = [
        'description',
        'owner_user',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function freeSpots()
    {
        return $this->hasMany(FreeSpot::class);
    }
}