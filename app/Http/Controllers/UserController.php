<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    static $isNewUser = false;

    //
    public function email($email)
    {
        $res = ['exists' => false, '_token' => session('_token')];
        if ($user = User::where('EMAIL', $email)->first()) {
            $res['exists'] = true;
            $res['user'] = $user;
            $res['isNewUser'] = static::$isNewUser;
        }
        return $res;
    }

    public function all()
    {
        return User::all();
    }

    public function regEmail()
    {
        $user = User::where('EMAIL', request('EMAIL'));

        if (!$user->exists()) {
            //登録
            User::create(
                [
                    'EMAIL' => request('EMAIL'),
                    'STARTTIME' => date('Y-m-d H:i:s'),
                ]
            );
            static::$isNewUser = true;
        }

        return $this->email(request('EMAIL'));
    }

    public function regEmailForm()
    {
        return view('user.regEmailForm');
    }
}

