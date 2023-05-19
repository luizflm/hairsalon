<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;
    
    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'cpf',
        'is_admin'
    ];

    protected $hidden = [
        'password'
    ];

    public function appointments() {
        return $this->hasMany(Appointment::class);
    }
}
