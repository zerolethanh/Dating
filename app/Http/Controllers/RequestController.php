<?php

namespace App\Http\Controllers;

use App\User;
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

    function pull()
    {
        $rs = \App\Request::where('to_email', request('to_email'))
            ->orderBy('request_time', 'desc')
            ->get();
        $numberOfRequests = count($rs);
        $from_emails = array_unique($rs->pluck('from_email')->toArray());
        $from_users = User::whereIn('EMAIL', $from_emails)->get();

        if ($numberOfRequests > 0) {
            $r = $rs[0];
            $r_user = User::where('EMAIL', $r['from_email'])->first();
        }

        return get_defined_vars();
    }

    function is_accepted()
    {
        $r = \App\Request::find(request('id'));
        $accepted = false;

        if ($r && $r["accepted"]) {
            $accepted = true;
        }
        return get_defined_vars();
    }

    function accept()
    {
        $r = \App\Request::find(request('id'));
        $r->accepted = 1;
        $r->save();

        return get_defined_vars();
    }
}
