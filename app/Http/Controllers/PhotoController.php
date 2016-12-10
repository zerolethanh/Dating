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
        $uploaded = false;
        if ($file = request()->file('photo')) {
            $path = $file->storeAs('public', Uuid::uuid() . '.' . $file->guessClientExtension());

            $realPath = str_replace('public', 'storage', asset($path));

//            return $realPath;
            $faceInfo = $this->getFaceAPI(
                $realPath
//                "http://133.242.226.127:2345/storage/d3bf5797-2f81-38cd-87e2-8330a53bdb22.png"
            );

//            $faceId = "7f03ba8d-22d7-4976-847f-c9da0bd1a1bf";
//            $faceIdList = "f114c8e1-394d-45ec-88ec-13c96dbbb053, aa24f7da-a1c1-4834-bb94-f9c907c7ee54, 49d77c8d-8b1c-4cf2-9600-6ccafa0b1c39,2aa863b0-faaa-44c8-b319-9848a87d71ac";
//            $faceFamilia = $this->getFaceFimiliar($faceId, $faceIdList);

            if (is_string($faceInfo)) {
                $faceInfo = json_decode($faceInfo, true);
            }

//            dd($faceInfo);
            $userInfoUpdated = false;
            if ($faceInfoCount = count($faceInfo)) {
                if ($email = request('EMAIL')) {
                    $user = User::where('EMAIL', $email)->first();
                    if ($user) {
                        try {
//                    dd($faceInfo);
//                        dd($faceInfo[0]["faceId"]);
                            $user->FACEID = $faceInfo[0]["faceId"];
                            $user->AGE = $faceInfo[0]['faceAttributes']['age'];
                            $user->GENDER = strtoupper($faceInfo[0]['faceAttributes']['gender']);
                            $user->IMAGE = $realPath;
                            $user->RECENTJOINTIME = date('Y-m-d H:i:s');

                            $user->save();
                            $userInfoUpdated = true;
                        } catch (\Exception $e) {
                            return $e;
                        }
                    }
                }
            }
            $uploaded = true;

            return get_defined_vars();
        }
        $error = 'photo filename is not valid';
        return get_defined_vars();
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

    function suggestion()
    {
//        $own = User::where('EMAIL', request('EMAIl'))->first();
        $own = User::where('EMAIL', 'thanh@gmail.com')->first()->toArray();
//        dd($own);
        return $this->getListUsersSuggestion($own);
    }

    function sug()
    {
//        $own = User::where('EMAIL', 'thanh@gmail.com')->first()->toArray();
        $own = User::where('EMAIL', request('EMAIL'))->first()->toArray();

        $sugList = User::where('GENDER', '<>', $own['GENDER'])
            ->where(function ($q) use ($own) {
                $q->where('AGE', '>=', $own["AGE"] - 3)
                    ->orWhere('AGE', '<=', $own["AGE"] + 3);
            }
            )->get();


        return $sugList;
    }

    function sugForm(){
        return view('photo.sugForm');
    }

}
