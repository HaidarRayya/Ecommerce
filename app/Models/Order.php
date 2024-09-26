<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'totalprice',
        'expectedArrivalDate',
        'customer_id',
        'seller_id',
        'deliveryDriver_id',
        'status'
    ];
    /* public function user()
    {
        return $this->belongsTo(User::class);
    }*/
}
