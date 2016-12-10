<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function email($email)
    {
        return User::where('email', $email)->first();

    }

    public function all()
    {
        return User::all();
    }
}
