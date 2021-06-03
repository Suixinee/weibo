<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class TestController extends Controller
{
    //
    public function test(){
       dump(Route::current());
       dump(Route::currentRouteName());
       dump(Route::currentRouteAction());

    }
}
