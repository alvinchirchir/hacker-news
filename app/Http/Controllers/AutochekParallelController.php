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
            $maxIndex=Http::get("https://hacker-news.firebaseio.com/v0/maxitem.json?print=pretty");

            //Serialize and parse to int
            $maxIDString=(string)$maxIndex;
            $maxIDint = (int)$maxIDString;

            for ($i = 0; $i < $total; $i++) {
                yield new Request('GET', "https://hacker-news.firebaseio.com/v0/item/$maxIDint.json?print=pretty");
                $maxIDint--;
            }
        };
        $count=0;
        $string="";
        $pool=new Pool($client, $requests(100), [
            'concurrency' => 5,
            'fulfilled' => function (Response $response, $index) use (&$count,&$string) {
                // this is delivered each successful response
                $item=json_decode($response->getBody());
                //Check if its a story
                if((string)$item->type=="story")
                {
                //Extract title
                //Check if title is defined
                $isDefined = isset($item->title);
                if($isDefined==1 &&$count<25){
                     $string .=(string)$item->title;
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
            $maxIndex=Http::get("https://hacker-news.firebaseio.com/v0/maxitem.json?print=pretty");

            //Serialize and parse to int
            $maxIDString=(string)$maxIndex;
            $maxIDint = (int)$maxIDString;

            for ($i = 0; $i < $total; $i++) {
                yield new Request('GET', "https://hacker-news.firebaseio.com/v0/item/$maxIDint.json?print=pretty");
                $maxIDint--;
            }
        };
        $count=0;
        $string="";
        $pool=new Pool($client, $requests(1000), [
            'concurrency' => 20,
            'fulfilled' => function (Response $response, $index) use (&$string) {
                // this is delivered each successful response
                $previous_week = strtotime("-1 week +1 day");
                $start_week = strtotime("last sunday midnight",$previous_week);

                $item=json_decode($response->getBody());
                echo "Hallel";
                echo ($item->time>=$previous_week);

                //Check if its a story
                if((int)$item->time>=$previous_week   )
                {
                //Extract title
                //Check if title is defined
                $isDefined = isset($item->title);
                if($isDefined==1){
                     $string .=(string)$item->title;
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


}