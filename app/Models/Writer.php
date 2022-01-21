<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Writer extends Model
{
    protected $table = "writer";
    protected $primaryKey = "id";
    public $timestamps = false;

    protected $fillable = [
        'writerNum', 'name', 'alipayAccount', 'qqAccount', 'wechatAccount', 'writerSituation', 'writerQuality',
    ];

}
