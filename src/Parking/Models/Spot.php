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
}