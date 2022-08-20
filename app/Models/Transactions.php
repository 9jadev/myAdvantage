<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customers;


class Transactions extends Model
{
    use HasFactory;
    // satatus  0  not completed 1 competed
    protected $table = 'transactions';
    protected $fillable = ["customer_id", "message", "type", "amount", "reference", "status"];

    public function customer()
    {
        return $this->belongsTo(Customers::class, "customer_id", "customer_id");
    }
}
