<?php

namespace App\Http\Controllers\Auth;


use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\ErrorCollection;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\Auth\CreateUserRequest;
use App\Notifications\SmsVerifiedNotification;
use App\Notifications\EmailVerifiedNotification;



class ApiAuthController extends Controller
{
    public function register (CreateUserRequest $request) {

        $confirmationCode = Str::random(10);

        $user = User::create([
            'name'                  => $request->name." ".$request->surname,
            'email'                 => $request->email,
            'phone'                 => $request->phone,
            'password'              => Hash::make($request->password),
            'email_verified_code'   => $confirmationCode,
            'phone_verified_code'   => $confirmationCode,
            'social_address'        => $request->socialAddress,
            'remember_token'        => $confirmationCode
        ]);

        Notification::fake();

        //Fake Sms
        $user->notify(new SmsVerifiedNotification($confirmationCode));
        Notification::assertSentTo($user, SmsVerifiedNotification::class, function ($notification, $channels) use($user) {
            Storage::put("Sms/".$user->id.'_sms_verification.txt', 'Your verification code is: '.$user->email_verified_code);
            return true;
        });

        //Fake email
        $user->notify(new EmailVerifiedNotification($confirmationCode));
        Notification::assertSentTo($user, EmailVerifiedNotification::class, function ($notification, $channels) use($user) {
            Storage::put("Email/".$user->id.'_sms_verification.txt', 'Your verification code is: '.$user->email_verified_code);
            return true;
        });
        return new UserResource($user);
    }

    public function login (LoginRequest $request) {


        if (Auth::attempt(['email' => $request->userName ,'password' => $request->userPassword])){
             Auth::users()->createToken('Laravel Personal Access Client')->accessToken;

        }else{
            return (new ErrorCollection(collect([])))->additional(["statusCode" => 422,"message" => "User Not Found"]);
        }
    }

    public function logout (Request $request) {
        $token = $request->user()->token();
        $token->revoke();
        $response = ['message' => 'You have been successfully logged out!'];
        return response($response, 200);
    }
}
