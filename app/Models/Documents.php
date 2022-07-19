<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documents extends Model
{
    use HasFactory;
    protected $table = "documents";
    protected $fillable = [
        "customer_id",
        "status",
        "type",
        "admin_id",
        "document_link",
    ];
}
