<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimPayment extends Model
{
    use HasFactory;

    protected $table = "claim_payments";
    protected $guarded = [];
    protected $with = ["claim"];

    public function claim()
    {
        return $this->hasOne(Claim::class, 'id', 'claim_id');
    }
}
