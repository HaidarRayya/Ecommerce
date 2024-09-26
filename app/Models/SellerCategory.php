<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerCategory extends Model
{
    use HasFactory;
    /*  public function seller()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function categoryProduct()
    {
        return $this->belongsTo(Product::class);
    }*/
    /* public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    */
    protected $fillable = [
        'seller_id',
        'category_id',
    ];
}
