<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HairdresserAvailability extends Model
{
    use HasFactory;

    protected $table = 'hairdresser_availability';

    public $timestamps = false;

    protected $fillable = [
        'weekday',
        'hours',
        'hairdresser_id',
    ];

    public function hairdresser() {
        return $this->belongsTo(Hairdresser::class);
    }
}
