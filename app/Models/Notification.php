<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    /* public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    */
    protected $fillable = [
        'user_id',
        'descripation',
        'seller_id',
        'date',
        'reading',
        'redirection',
        'data'
    ];
}
