<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimAssignment extends Model
{
    use HasFactory;
    protected $table = "claim_assignments";
    protected $guarded = [];
}
