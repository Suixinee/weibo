<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::group([],function(){
    Route::get('test',[\App\Http\Controllers\TestController::class,'test'])->name('retest');
    Route::get('aa',function(){
        // return redirect()->to('test');
        // return redirect()->route('retest');
    });
});

Route::fallback(function(){
    echo '404';
});

