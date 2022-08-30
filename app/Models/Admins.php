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
    protected $appends = [
        "accesslog",
    ];

    public function getAccesslogAttribute()
    {
        if ($this->admin_type == 0 || $this->admin_type == null) {
            return ["Dashboard", "Payments", "Members", "Claims & Rewards", "Users Roles", "FAQS"];
        }
        if ($this->admin_type == 1) {
            return ["Dashboard", "Members", "Claims & Rewards", "FAQS"];
        }
        if ($this->admin_type == 2) {
            return ["Dashboard", "FAQS"];
        }

    }
}
