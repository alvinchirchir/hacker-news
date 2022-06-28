<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;


class AutochekParallelController extends Controller
{
    public function mostOccuringLastTwentyFive()
    {

        $client = new Client();
        //$response = $client->get('http://httpbin.org/get');

        $requests = function ($total) {
            $maxIndex = Http::get("https://hacker-news.firebaseio.com/v0/maxitem.json?print=pretty");

            //Serialize and parse to int
            $maxIDString = (string)$maxIndex;
            $maxIDint = (int)$maxIDString;

            for ($i = 0; $i < $total; $i++) {
                yield new Request('GET', "https://hacker-news.firebaseio.com/v0/item/$maxIDint.json?print=pretty");
                $maxIDint--;
            }
        };
        $count = 0;
        $string = "";
        $pool = new Pool($client, $requests(100), [
            'concurrency' => 5,
            'fulfilled' => function (Response $response, $index) use (&$count, &$string) {
                // this is delivered each successful response
                $item = json_decode($response->getBody());
                //Check if its a story
                if ((string)$item->type == "story") {
                    //Extract title
                    //Check if title is defined
                    $isDefined = isset($item->title);
                    if ($isDefined == 1 && $count < 25) {
                        $string .= (string)$item->title;
                        $count++;
                    }
                }
            },
            'rejected' => function (RequestException $reason, $index) {
                // this is delivered each failed request
                echo $reason;
            },
        ]);
        // Initiate the transfers and create a promise
        $promise = $pool->promise();
        // Force the pool of requests to complete.
        $promise->wait();
        //Separate long sentence into array of words
        $delimiter = ' ';
        $words = explode($delimiter, $string);
        $wordCount = array_count_values($words);
        arsort($wordCount);
        // Get the top 20 words
        $wordCount = array_splice($wordCount, 0, 10);
        return $wordCount;
    }


    public function mostOccuringLastWeek()
    {

        $client = new Client();
        //$response = $client->get('http://httpbin.org/get');

        $requests = function ($total) {
            $maxIndex = Http::get("https://hacker-news.firebaseio.com/v0/maxitem.json?print=pretty");

            //Serialize and parse to int
            $maxIDString = (string)$maxIndex;
            $maxIDint = (int)$maxIDString - 50000;

            for ($i = 0; $i < $total; $i++) {
                yield new Request('GET', "https://hacker-news.firebaseio.com/v0/item/$maxIDint.json?print=pretty");
                $maxIDint--;
            }
        };
        $count = 0;
        $string = "";
        $pool = new Pool($client, $requests(1000), [
            'concurrency' => 20,
            'fulfilled' => function (Response $response, $index) use (&$string) {
                // this is delivered each successful response
                $previous_week = strtotime("-1 week +1 day");
                $start_week = strtotime("last sunday midnight", $previous_week);
                $end_week = strtotime("next saturday", $start_week);
                $item = json_decode($response->getBody());
                //Check if its a story
                if ((int)$item->time >= $start_week && (int)$item->time <= $end_week) {
                    //Extract title
                    //Check if title is defined
                    $isDefined = isset($item->title);
                    if ($isDefined == 1) {
                        $string .= (string)$item->title;
                    }
                }
            },
            'rejected' => function (RequestException $reason, $index) {
                // this is delivered each failed request
                echo $reason;
            },
        ]);
        // Initiate the transfers and create a promise
        $promise = $pool->promise();
        // Force the pool of requests to complete.
        $promise->wait();
        //Separate long sentence into array of words
        $delimiter = ' ';
        $words = explode($delimiter, $string);
        $wordCount = array_count_values($words);
        arsort($wordCount);
        // Get the top 20 words
        $wordCount = array_splice($wordCount, 0, 10);
        return $wordCount;
    }
    public function getUserData($user, $item)
    {
        return $item;
    }


    public function mostOccuringWithHighKarma1()
    {
        $client = new Client();

        $requests = function ($total) use ($client) {
            $response = Http::get("https://hacker-news.firebaseio.com/v0/newstories.json?print=pretty");
            for ($i = 0; $i < $total; $i++) {
                $id = $response[$i];
                // Send an asynchronous request.
                $request = new \GuzzleHttp\Psr7\Request('GET', "https://hacker-news.firebaseio.com/v0/item/$id.json?print=pretty");
                $promise = $client->sendAsync($request)->then(function (Response $response) {
                    $item = json_decode($response->getBody());
                    $userResponse = Http::get("https://hacker-news.firebaseio.com/v0/user/$item->by.json?print=pretty");
                    $user = json_decode($userResponse->getBody());
                    //return $this->getUserData($user,$item); 

                });

                yield $promise->wait();
            }
        };
        $client = new Client();
        $string = "";
        $pool = new Pool($client, $requests(20), [
            'concurrency' => 20,
            'fulfilled' => function (Response $response, $index) {

                $final = json_decode($response->getBody());
                echo $final;

                // this is delivered each successful response
                //$item=json_decode($response->getBody());
                //$response=Http::get("https://hacker-news.firebaseio.com/v0/user/$$item->by.json?print=pretty");

                //$user=json_decode($res->getBody());
                //echo $item;


            },
            'rejected' => function (RequestException $reason, $index) {
                // this is delivered each failed request
                echo "Unsuccessful";
            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();
        // Force the pool of requests to complete.
        $promise->wait();
    }
    public function mostOccuringWithHighKarma()
    {
        $client = new Client();
        $string="";

        //Get total requests to be made from new stories and ids
        $response = $client->get('https://hacker-news.firebaseio.com/v0/newstories.json?print=pretty');
        $newStories = json_decode($response->getBody());
        $max_count=count($newStories);
        //Asynchronously fetch stories while fetching the author
                $requests = function ($total) use ($client,&$string,$newStories) {
            $uri = 'https://hacker-news.firebaseio.com/v0/newstories.json?print=pretty';
            for ($i = 0; $i < $total; $i++) {
                yield function () use ($client, $i,&$string,&$newStories) {
                    return $client->getAsync("https://hacker-news.firebaseio.com/v0/item/$newStories[$i].json?print=pretty")->then(function (Response $response) use (&$string,$client) {

                        $item = json_decode($response->getBody());

                        $res = $client->get("https://hacker-news.firebaseio.com/v0/user/$item->by.json?print=pretty");
                        $user = json_decode($res->getBody());

                        if($user->karma>=10){

                        $string .= (string)$item->title;

                        }
                    });
                };
            }
        };


        $pool = new Pool($client, $requests(20),['concurrency' => 5]);
        // Initiate the transfers and create a promise
        $promise = $pool->promise();
        // Force the pool of requests to complete.
        $promise->wait();

         $delimiter = ' ';
         $words = explode($delimiter, $string);
         $wordCount = array_count_values($words);
         arsort($wordCount);
         // Get the top 20 words
         $wordCount = array_splice($wordCount, 0, 10);
         return $wordCount;

    }


    public function mostOccuringLastTwentyFour()
    {
        $client = new Client();
        $string="";

        //Get total requests to be made from new stories and ids
        $response = $client->get('https://hacker-news.firebaseio.com/v0/maxitem.json?print=pretty');
        $maxIndex = json_decode($response->getBody());
        $count=0;
        //Asynchronously fetch stories while fetching the author
            $requests = function () use ($client,&$string,&$count,&$maxIndex) {
            $uri = 'https://hacker-news.firebaseio.com/v0/newstories.json?print=pretty';
            while($count<2){
                yield function () use ($client,&$count,&$string,&$maxIndex) {
                    return $client->getAsync("https://hacker-news.firebaseio.com/v0/item/$maxIndex.json?print=pretty")->then(function (Response $response) use (&$string,&$count) {

                        $item = json_decode($response->getBody());
                        echo $item->id;
                        $count=$count+1;

                        //$res = $client->get("https://hacker-news.firebaseio.com/v0/user/$item->by.json?print=pretty");
                        // $user = json_decode($res->getBody());

                        // if($user->karma>=10){

                        // $string .= (string)$item->title;

                        // }
                    });
                };
            }
        };


        $pool = new Pool($client, $requests(),['concurrency' => 5]);
        // Initiate the transfers and create a promise
        $promise = $pool->promise();
        // Force the pool of requests to complete.
        $promise->wait();

        //  $delimiter = ' ';
        //  $words = explode($delimiter, $string);
        //  $wordCount = array_count_values($words);
        //  arsort($wordCount);
        //  // Get the top 20 words
        //  $wordCount = array_splice($wordCount, 0, 10);
        //  return $wordCount;

    }


    
}
