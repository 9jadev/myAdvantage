<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payments extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "payments";
    protected $with = ["plan"];
    protected $fillable = [
        "customer_id",
        "amount",
        "plan_id",
        "reference",
        "status",
        "firstname",
        "lastname",
        "image",
        "bank_name",
        "bank_account",
    ];

    protected $hidden = [
        'deleted_at',
    ];

    public function plan()
    {
        return $this->hasOne(Plans::class, "id", "plan_id");
    }
}
