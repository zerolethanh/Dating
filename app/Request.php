<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    //
    protected $table = "requests";

    public $timestamps = false;

    protected $guarded = ['id'];

}
