<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HairdresserDoneService extends Model
{
    use HasFactory;

    protected $table = 'hairdresser_done_services';

    public $timestamps = false;

    protected $fillable = [
        'service_datetime',
        'hairdresser_id',
        'hairdresser_service_id',
    ];

    public function hairdresser() {
        return $this->belongsTo(Hairdresser::class);
    }

    public function service() {
        return $this->belongsTo(HairdresserService::class, 'hairdresser_service_id', 'id');
    }
}
