<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HairdresserEvaluation extends Model
{
    use HasFactory;

    protected $table = 'hairdresser_evaluations';

    public $timestamps = false;

    protected $fillable = [
        'stars',
        'comment',
        'user_id',
        'hairdresser_id',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function hairdresser() {
        return $this->belongsTo(Hairdresser::class);
    }
}
