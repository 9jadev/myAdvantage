<?php

namespace App\Models;

use App\Models\Customers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kyc extends Model
{
    use HasFactory;
    protected $table = "kycs";
    protected $fillable = [
        "customer_id",
        "nationality",
        "state_of_residence",
        "house_address",
        "upliner",
        "community_interest",
        "future_aspiration",
        "discount_preferences",
        "pre_existing_health_condtion",
        "pre_existing_health_condtion_drug",
        "allergy",
        "next_of_kin_name",
        "next_of_kin_phone_number",
        "vbank_account_number",
        "pharmacy_location",
        "pharmacy_name"
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, "customer_id", "customer_id");
    }
}
