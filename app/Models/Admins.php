<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admins extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = "admins";
    protected $fillable = [
        'firstname',
        'lastname',
        'phone_number',
        "email",
        "admin_type",
        'password',
    ];
}
