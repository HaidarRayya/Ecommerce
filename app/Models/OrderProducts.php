<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProducts extends Model
{
    use HasFactory;
    protected $fillable = [
        'quantity',
        'price',
        'product_id',
        'order_id'
    ];
}
