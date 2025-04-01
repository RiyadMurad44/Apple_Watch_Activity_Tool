<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityData extends Model
{
    use HasFactory;

    // protected $table = 'activity_data';

    protected $fillable = [
        'date', 'steps', 'distance_km', 'active_minutes',
    ];
}
