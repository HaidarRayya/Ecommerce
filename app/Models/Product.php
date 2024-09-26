<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    /* public function seller()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }*/
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'category_id',
        'category_type',
        'category_name',
        'price',
        'count',
        'image',
        'seller_id',
        'seller_name',
        'description',
        'evaluation'
    ];
}
