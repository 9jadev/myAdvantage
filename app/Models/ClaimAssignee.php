<?php

namespace App\Models;

use App\Models\Claim;
use App\Models\Customers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimAssignee extends Model
{
    use HasFactory;

    protected $table = "claim_assignees";
    protected $guarded = [];
    protected $hidden = ['deleted_at'];
    protected $with = ["customer", "claims"];

    public function customer()
    {
        return $this->belongsTo(Customers::class, "customer_id", "customer_id");
    }

    public function claims()
    {
        return $this->belongsTo(Claim::class, "claim_id", "id");
    }
}
