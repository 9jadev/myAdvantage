<?php

namespace App\Models;

use App\Models\Documents as ModelsDocuments;
use App\models\Kyc;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customers extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = "customers";

    protected $fillable = [
        'firstname',
        'lastname',
        'phone_number',
        'upliner',
        'customer_id',
        'referral_code',
        'status',
        'email',
        'password',
    ];
    protected $appends = [
        "checkbvn",
        "checkid",
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function kyc()
    {
        return $this->hasOne(Kyc::class, "customer_id", "customer_id");
    }

    public function getCheckidAttribute()
    {
        $checkdocuments = ModelsDocuments::where("customer_id", $this->customer_id)->where("type", "1")->where("status", "1")->first();
        if (!$checkdocuments) {
            return false;
        }
        return true;
    }

    public function getCheckbvnAttribute()
    {
        $checkdocuments = Documents::where("customer_id", $this->customer_id)->where("type", "0")->where("status", "1")->first();
        if (!$checkdocuments) {
            return false;
        }
        return true;
    }

}
