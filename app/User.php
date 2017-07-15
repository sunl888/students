<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    //设置可以批量赋值
    protected $guarded = [];
}
