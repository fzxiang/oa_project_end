<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = "order";
    protected $primaryKey = "id";
    public $timestamps = false;

    protected $fillable = [
        'invoice', 'acceptUser', 'aliOrder', 'settleState', 'memberName', 'taobaoPrice', 'customerContact',
        'orderOutline', 'remarks', 'paymentTime', 'receivingTime'
    ];

}
