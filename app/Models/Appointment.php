<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'ap_datetime',
        'was_done',
        'user_id',
        'hairdresser_id',
        'hairdresser_service_id',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function hairdresser() {
        return $this->belongsTo(Hairdresser::class);
    }

    public function service() {
        return $this->belongsTo(HairdresserService::class, 'hairdresser_service_id', 'id');
    }
}
