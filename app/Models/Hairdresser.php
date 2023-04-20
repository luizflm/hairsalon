<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hairdresser extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'specialties',
        'avatar',
    ];

    public function services() {
        return $this->hasMany(HairdresserService::class);
    }

    public function evaluations() {
        return $this->hasMany(HairdresserEvaluation::class);
    }

    public function doneServices() {
        return $this->hasMany(HairdresserDoneService::class);
    }

    public function availability() {
        return $this->hasMany(HairdresserAvailability::class);
    }

    public function appointments() {
        return $this->hasMany(Appointment::class);
    }
}
