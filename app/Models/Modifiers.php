<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modifiers extends Model
{
    use HasFactory;

    protected $table = "modifiers";
    protected $guarded = ['id']; 
    public $timestamps = false;
}
