<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemperatureRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'temperature',
        'checked_by',
        'checked_at',
        'notes',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'temperature' => 'float',
    ];
}
