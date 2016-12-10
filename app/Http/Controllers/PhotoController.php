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

            $realPath = str_replace('public', 'storage', asset($path));

//            return $realPath;
            $faceInfo = $this->getFaceAPI($realPath);

            return $faceInfo;
        }

        return [
            'msg' => 'file photo not found on request'
        ];
    }

    public function getFaceAPI($path)
    {
        $request = new \HTTP_Request2('https://api.projectoxford.ai/face/v1.0/detect');
        $url = $request->getUrl();

        $headers = array(
            // Request headers
            'Content-Type' => 'application/json',
            'Ocp-Apim-Subscription-Key' => '2968a3fde555436aad8348f13a82a108',
        );

        $request->setHeader($headers);

        $parameters = array(
            // Request parameters
            'returnFaceId' => 'true',
            'returnFaceLandmarks' => 'false',
            'returnFaceAttributes' => 'age,gender',
        );

        $url->setQueryVariables($parameters);

        $request->setMethod(\HTTP_Request2::METHOD_POST);

// Request body
        $request->setBody("{\"url\": \"$path\"}");

//        dd($request);

        try {
            $response = $request->send();
            return $response->getBody();
        } catch (\HttpException $ex) {
            return $ex;
        }
    }

}
