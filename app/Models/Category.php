<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    /*  public function seller()
    {
        return $this->belongsTo(User::class);
    }*/
    /* public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    */
    protected $fillable = [
        'name',
        'type'
    ];
}
