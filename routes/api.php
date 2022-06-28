<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Route::get('autochek', 'AutochekController@index');
// Route::get('autochek/{article}', 'AutochekController@show');
// Route::post('autochek', 'AutochekController@store');
// Route::put('autochek/{article}', 'AutochekController@update');
// Route::delete('autochek/{article}', 'AutochekController@delete');

Route::get('/last-twenty-five', 'App\Http\Controllers\AutochekParallelController@mostOccuringLastTwentyFive')->name('last-twenty-five');
Route::get('/last-week', 'App\Http\Controllers\AutochekParallelController@mostOccuringLastWeek')->name('last-week');
Route::get('/top/users/karmas/stories', 'App\Http\Controllers\AutochekParallelController@mostOccuringWithHighKarma')->name('/top/users/karmas/stories');



Route::get('/', function () {
    return "view('posts.index')";
});
