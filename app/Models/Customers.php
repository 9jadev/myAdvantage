<?php

namespace App\Models;

use App\Models\Documents as ModelsDocuments;
use App\Models\Kyc;
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
        'postal_address',
        'gender',
        'lastname',
        'phone_number',
        'upliner',
        'customer_id',
        'referral_code',
        "id_document",
        "bvn",
        "level",
        'status',
        'email',
        'password',

    ];
    protected $appends = [
        "checkbvn",
        "balance",
        "customerlevel",
        "subscripionstatus",
        "checkid",
        "checkyc",
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

    public function getBalanceAttribute()
    {
        return Wallet::where("customer_id", $this->customer_id)->first("balance")->balance;
    }

    public function getCheckycAttribute()
    {
        return $this->kyc != null ? true : false;
    }

    public function getCustomerlevelAttribute()
    {
        // return $this-> != null ? true : false;
        if ($this->level == "0" || $this->level == null) {
            return "Newbies";
        }
        if ($this->level == "1") {
            return "Starter";
        }
        if ($this->level == "2") {
            return "Rookie";
        }
        if ($this->level == "3") {
            return "Star";
        }

        if ($this->level == "4") {
            return "Bronze";
        }

        if ($this->level == "5") {
            return "Silver";
        }

        if ($this->level == "6") {
            return "Gold";
        }

        if ($this->level == "7") {
            return "Platinum";
        }

    }

    public function getCheckidAttribute()
    {
        $checkdocuments = ModelsDocuments::where("customer_id", $this->customer_id)->where("type", "1")->where("status", "1")->first();
        if (!$checkdocuments) {
            return false;
        }
        return true;
    }

    public function getSubscripionstatusAttribute()
    {
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
