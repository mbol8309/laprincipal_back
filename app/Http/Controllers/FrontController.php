<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function show($item)
    {
        return [
            'data'=>[
                'id'=>$item,
                'items'=>config("front.$item")
            ]
        ];
    }

    public function index()
    {
        $data = config("front");

        return [
            'data'=>[
                'id'=>'front',
                // 'items'=>
            ]
        ];
    }
}
