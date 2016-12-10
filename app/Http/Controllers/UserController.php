<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function email($email)
    {
        $res = ['exists' => false, '_token' => session('_token')];
        if ($user = User::where('email', $email)->first()) {
            $res['exists'] = true;
            $res['user'] = $user;
        }
        return $res;
    }

    public function all()
    {
        return User::all();
    }
}
