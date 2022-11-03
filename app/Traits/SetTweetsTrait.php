<?php

namespace App\Traits;

use GuzzleHttp\Client;
use App\Models\Twitter;
use Illuminate\Support\Carbon;

trait SetTweetsTrait {

   public function fun1(){

      return "Trait response";
   }

   public function connectAndgetUserTweets(int $userId){
      $client = new Client();
      $response = $client->request('GET', 'https://63633ad166f75177ea3ff897.mockapi.io/api/v1/tweet');
      $tweets = json_decode($response->getBody()->getContents());
      $tweets = collect($tweets)->map(function ($tweet) use ($userId) {

         $tweet->user_id = $userId;
         $tweet->status = 0;
     
         return (array)$tweet;
     });

     return Twitter::insert($tweets->all()) ? true : false;
   }

}