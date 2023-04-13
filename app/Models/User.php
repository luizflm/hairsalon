<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    
    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
    ];

    protected $hidden = [
        'password'
    ];

    public function evaluations() {
        return $this->hasMany(HairdresserEvaluation::class);
    }

    public function appointments() {
        return $this->hasMany(Appointment::class);
    }

    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [];
    }
}
