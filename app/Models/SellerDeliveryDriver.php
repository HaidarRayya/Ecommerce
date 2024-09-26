<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerDeliveryDriver extends Model
{
    use HasFactory;
    protected $fillable = [
        'seller_id',
        'deliveryDriver_id',
    ];
}