<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Walletlimit extends Model
{
    use HasFactory;

    protected $table = "walletlimits";
    protected $fillable = ["max_top_up", "max_withdrawal"];
}
