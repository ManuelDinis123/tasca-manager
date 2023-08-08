<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemsModifiers extends Model
{
    use HasFactory;

    protected $table = "order_items_modifiers";
    protected $guarded = ['id']; 
    public $timestamps = false;
}
