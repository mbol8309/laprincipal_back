<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Settings extends Model
{
    use HasFactory;

    public static function groups()
    {
        $groups = DB::select("select `group` from settings group by `group`"); //TODO: Find another way
        $groups = array_map(function($g){
            return [
                'id'=>$g->group,
                'name'=>$g->group
            ];
        },$groups);
        return $groups;
    }

    public static function items($group)
    {
        return Settings::where('group',$group)->get();
    }
}
