<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $table = "shop";
    protected $primaryKey = "shop_id";

    protected $fillable = [
        'shop_name', 'company_name', 'remarks', 'create_user', 'update_user'
    ];

}
