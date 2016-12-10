<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    //
    public function email($email)
    {
        $res = ['exists' => false, '_token' => session('_token')];
        if ($user = User::where('EMAIL', $email)->first()) {
            $res['exists'] = true;
            $res['user'] = $user;
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
//            dd(request()->all());
//            dd(Schema::getColumnListing('USERS'));
//            $data = array_only(request()->all(), Schema::getColumnListing('USERS'));
//            unset($data['EMAIL']);
//            if (count($data)) {
//                $user->update($data);
//            }
            User::create(
                [
                    'EMAIL' => request('EMAIL'),
                    'STARTTIME' => date('Y-m-d H:i:s'),
                ]
            );
        }

        return $this->email(request('EMAIL'));
    }

    public function regEmailForm()
    {
        return view('user.regEmailForm');
    }
}

