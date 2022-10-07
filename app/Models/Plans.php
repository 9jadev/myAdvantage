<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plans extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "plans";
    // protected $with = ["claims"];
    protected $fillable = [
        "plan_name",
        "tenor",
        "plan_amount",
        "pay_days",
    ];

    protected $hidden = [
        'deleted_at',
    ];

    public function claims()
    {
        return $this->hasMany(ClaimPayment::class, 'plan_id', 'id');
    }

}
