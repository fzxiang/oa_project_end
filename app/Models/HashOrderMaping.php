<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HashOrderMaping extends Model
{
    protected $table = "hash_order_maping";
    protected $primaryKey = "strHash";
    public $timestamps = false;

    protected $fillable = [
        'strHash', 'orderIds', 'operate'
    ];

}
