<?php

namespace App\Http\Controllers\Auth;


use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    public function login (Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $response = ['token' => $token];
                return response($response, 200);
            } else {
                $response = ["message" => "Password mismatch"];
                return response($response, 422);
            }
        } else {
            $response = ["message" =>'User does not exist'];
            return response($response, 422);
        }
    }

    public function logout (Request $request) {
        $token = $request->user()->token();
        $token->revoke();
        $response = ['message' => 'You have been successfully logged out!'];
        return response($response, 200);
    }
}
