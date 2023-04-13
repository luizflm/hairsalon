<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HairdresserService extends Model
{
    use HasFactory;

    protected $table = 'hairdresser_services';

    public function getPriceAttribute()
    {
        return $this->attributes['price'] / 100;
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value * 100;
    }

    public $timestamps = false;

    protected $fillable = [
        'name',
        'price',
        'hairdresser_id',
    ];

    public function hairdresser() {
        return $this->belongsTo(Hairdresser::class);
    }

    public function doneServices() {
        return $this->hasMany(HairdresserService::class, 'hairdresser_service_id', 'id');
    }

    public function appointments() {
        return $this->hasMany(Appointment::class);
    }
}
