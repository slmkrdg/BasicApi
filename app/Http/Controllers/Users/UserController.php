<?php

namespace App\Http\Controllers\Users;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\SetTweetsTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\TweetsResource;
use App\Http\Resources\ErrorCollection;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ValidationResource;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\Users\CreateUserRequest;
use App\Notifications\SmsVerifiedNotification;
use App\Notifications\EmailVerifiedNotification;
use App\Http\Requests\Users\ValidationUserRequest;
use App\Http\Requests\Users\UpdateUserTweetRequest;
use App\Models\Twitter;

class UserController extends Controller
{
    use SetTweetsTrait;
    
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
            Storage::put("Email/".$user->id.'_email_verification.txt', 'Your verification code is: '.$user->email_verified_code);
            return true;
        });

        return (new UserResource($user))->additional(["statusCode" => 201,"message" => "User created"]);
    }

    public function validation(ValidationUserRequest $request)
    {
        $user = User::where($request->email ? "email_verified_code" : "phone_verified_code",$request->verificationCode)
                    ->whereNull($request->email ? "email_verified_at" : "phone_verified_at")->first();
        if($user){
            return $user->update([$request->email ? "email_verified_at" : "phone_verified_at" => now() ])
                ? (new ValidationResource(collect([])))->additional(["statusCode" => 200,"message" => "User validate"])
                : (new ErrorCollection(collect([])))->additional(["statusCode" => 422,"message" => "User Not validated"]);
        }
        
        return (new ErrorCollection(collect([])))->additional(["statusCode" => 422,"message" => "User Not Found"]);
    }

    public function getUserTweets(Request $request)
    {
        return (new TweetsResource(Auth::user()->tweets()->skip((($request->pageNumber ?? 1) -1) * 20)->take(20)->get()))
            ->additional(["statusCode" => 200,"message" => "User Tweets"]);
    }

    public function setUserTweets()
    {
        return $this->connectAndgetUserTweets(Auth::user()->id)
            ? (new TweetsResource(collect([])))->additional(["statusCode" => 200,"message" => "User Tweets"])
            : (new ErrorCollection(collect([])))->additional(["statusCode" => 422,"message" => "Fail"]);
    }
    public function updateUserTweets(UpdateUserTweetRequest $request)
    {
        return Twitter::where("id",$request->tweetId)->update([
                'status'  => $request->status,
                'title'   => $request->title,
                'content' => $request->content,
            ])
            ? (new TweetsResource(collect([])))->additional(["statusCode" => 200,"message" => "User Tweet update"])
            : (new ErrorCollection(collect([])))->additional(["statusCode" => 422,"message" => "Fail"]);
    }
}
