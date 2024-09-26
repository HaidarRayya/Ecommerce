<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestCategory extends Model
{
    use HasFactory;

    /* public function category()
    {
        return $this->belongsTo(Category::class);
    }*/
    protected $fillable = [
        'seller_id',
        'name',
        'type'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
