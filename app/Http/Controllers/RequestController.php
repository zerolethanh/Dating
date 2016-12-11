<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class RequestController extends Controller
{
    //

    function request()
    {
//        return request()->all();
        $request_time = date('Y-m-d H:i:s');
        $data = request()->all() + compact('request_time');

//        dd($data);

        return \App\Request::create(
            $data
        );
    }
}
