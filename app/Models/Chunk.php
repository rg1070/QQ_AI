<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chunk extends Model
{
    use HasFactory;

    protected $primaryKey = 'id'; // specify the non-integer primary key
    public $incrementing = false; // disable auto-incrementing

    protected $fillable = [
        'chunk', 'source', 'id'
    ];
}
