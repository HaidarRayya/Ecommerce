<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEvaluation extends Model
{
    use HasFactory;
    protected $fillable = [
        'evaluation',
        'customer_id',
        'seller_id'
    ];
}