<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Settings;

class SettingsController extends Controller
{
    public function groups(Request $request)
    {
        $groups = DB::select("select `group`,max(id) 'id' from settings group by `group`"); //TODO: Find another way
        return [
            "data"=>$groups
        ];
    }
}
