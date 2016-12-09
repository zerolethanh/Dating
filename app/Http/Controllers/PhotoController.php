<?php

namespace App\Http\Controllers;

use Faker\Provider\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    //
    public function uploadForm()
    {
        return view('photo.uploadForm');
    }

    public function upload()
    {
        if ($file = request()->file('photo')) {
            $path = $file->storeAs('public', Uuid::uuid() . '.' . $file->guessClientExtension());
            return str_replace('public', 'storage', asset($path));
        }

        return [
            'msg' => 'file photo not found on request'
        ];
    }

}
