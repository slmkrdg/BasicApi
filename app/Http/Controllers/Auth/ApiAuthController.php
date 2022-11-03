<?php

namespace App\Http\Controllers\Auth;


use Illuminate\Support\Facades\Auth;
use App\Http\Resources\TokenResource;
use App\Http\Resources\ErrorCollection;
use Psr\Http\Message\ServerRequestInterface;
use Laravel\Passport\Http\Controllers\AccessTokenController;



class ApiAuthController extends AccessTokenController
{
    public function login (ServerRequestInterface $request) {

        if (Auth::attempt(['email' => request()->userName ,'password' => request()->userPassword]) && (Auth::user()?->phone_verified_at || Auth::user()?->email_verified_at) ){

            $createRequest      = $request->withParsedBody([
                'grant_type'    => 'password',
                'client_id'     => request()->clientId,
                'client_secret' => request()->secretKey,
                'username'      => request()->userName,
                'password'      => request()->userPassword,
                'scope'         => "users"
            ]);
            $tokenResponse      = parent::issueToken($createRequest);
            $tokenJsonData      = $tokenResponse->getContent();
            $tokenCollectData   = collect(json_decode($tokenJsonData,true));

            return $tokenCollectData->isNotEmpty()
                ? (new TokenResource($tokenCollectData->only(["access_token","refresh_token"])))->additional(["statusCode" => 200,"message" => "Token created"])
                : (new ErrorCollection(collect([])))->additional(["statusCode" => 422,"message" => "Token Error"]);
        }else{
            return (new ErrorCollection(collect([])))->additional(["statusCode" => 422,"message" => "User Not Found"]);
        }
    }
}
