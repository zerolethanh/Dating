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

            $this->getFaceAPI($path);
            return str_replace('public', 'storage', asset($path));
        }

        return [
            'msg' => 'file photo not found on request'
        ];
    }
    public function getFaceAPI($path){
        $request = new Http_Request2('https://api.projectoxford.ai/face/v1.0/detect');
        $url = $request->getUrl();

        $headers = array(
            // Request headers
            'Content-Type' => 'application/json',
            'Ocp-Apim-Subscription-Key' => '{2968a3fde555436aad8348f13a82a108}',
        );

        $request->setHeader($headers);

        $parameters = array(
            // Request parameters
            'returnFaceId' => 'true',
            'returnFaceLandmarks' => 'false',
            'returnFaceAttributes' => '{age,gender}',
        );

        $url->setQueryVariables($parameters);

        $request->setMethod(HTTP_Request2::METHOD_POST);

// Request body
        $request->setBody("{url:http://topanhdep.net/wp-content/uploads/2015/12/anh-girl-xinh-gai-dep-98-18.jpg}");

        try
        {
            $response = $request->send();
            echo $response->getBody();
        }
        catch (HttpException $ex)
        {
            echo $ex;
        }
    }

}
