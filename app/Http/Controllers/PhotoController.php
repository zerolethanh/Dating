<?php

namespace App\Http\Controllers;

use App\User;
use Faker\Provider\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    const MAPPING_AGE = 3;
    const MALE = "MALE";
    const FEMALE = "FEMALE";

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

//            $faceId = "7f03ba8d-22d7-4976-847f-c9da0bd1a1bf";
//            $faceIdList = "f114c8e1-394d-45ec-88ec-13c96dbbb053, aa24f7da-a1c1-4834-bb94-f9c907c7ee54, 49d77c8d-8b1c-4cf2-9600-6ccafa0b1c39,2aa863b0-faaa-44c8-b319-9848a87d71ac";
//            $faceFamilia = $this->getFaceFimiliar($faceId, $faceIdList);

            if ($email = request('EMAIL')) {
                $user = User::where('EMAIL', $email)->first();
                try {
                    $user->FACEID = $faceInfo["faceId"];
                    $user->AGE = $faceInfo['faceAttributes']['age'];
                    $user->GENDER = $faceInfo['faceAttributes']['gender'];

                    $user->save();
                } catch (\Exception $e) {
                    return $e;
                }
            }
            if (is_string($faceInfo)) {
                $faceInfo = json_decode($faceInfo, true);
            }
            return compact('faceInfo', 'faceFamilia', 'user');
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

    public function getFaceFimiliar($faceId, $faceList)
    {
        $request = new \HTTP_Request2('https://api.projectoxford.ai/face/v1.0/findsimilars');
        $url = $request->getUrl();

        $headers = array(
            // Request headers
            'Content-Type' => 'application/json',
            'Ocp-Apim-Subscription-Key' => '2968a3fde555436aad8348f13a82a108',
        );

        $request->setHeader($headers);

        $parameters = array(// Request parameters
        );

        $url->setQueryVariables($parameters);

        $request->setMethod(\HTTP_Request2::METHOD_POST);

// Request body
        $request->setBody("{

        \"faceId\":\"$faceId\",
        \"faceListId\":\"$faceList\",
        \"maxNumOfCandidatesReturned\":10,
        \"mode\": \"matchFace\"
        }");

        try {
            $response = $request->send();
            echo $response->getBody();
        } catch (\HttpException $ex) {
            echo $ex;
        }
    }


    function getListFacesData()
    {
        $facesData = User::all();
        return $facesData;
    }

    function isMappingUser($own, $other)
    {
        $isMap = false;
        $diff_age = $own["AGE"] - $other["AGE"];
        if (abs($diff_age) <= self::MAPPING_AGE) {
            $isMap = true;
        }
        if ($isMap) {
            if ($own["GENDER"] == self::MALE || $own["GENDER" == self::FEMALE]) {
                if ($own["GENDER"] == $other["GENDER"]) {
                    $isMap = false;
                }
            }

        } else {
            $isMap = true;
        }
        return $isMap;
    }

    function isSameUser($own, $other)
    {
        $same = true;
        foreach ($own as $key => $value) {
            if ($other[$key] != $value) {
                $same = false;
            }
        }
        return $same;
    }

    function getListUsersSuggestion($own)
    {
        $usersSuggest = array();
        $usersList = $this->getListFacesData();
        foreach ($usersList as $key => $other) {
            if ($this->isSameUser($own, $other)) {
                continue;
            }
            if ($this->isMappingUser($own, $other)) {
                $usersSuggest[$key] = $other;
            }
        }
        return $usersSuggest;
    }

}
