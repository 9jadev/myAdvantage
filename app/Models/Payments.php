<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customers;
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

    // protected $appends = [
    //     'firstname',
    //     'lastname',
    // ];

    protected $hidden = [
        'deleted_at',
    ];

    public function plan()
    {
        return $this->hasOne(Plans::class, "id", "plan_id");
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, "customer_id", "customer_id");
    }

    // public static function selectSome(Array $data) {
    //     self::customer->select($data);
    // }
}
