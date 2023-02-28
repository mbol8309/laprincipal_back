<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Database\Eloquent\Concerns\HasGlobalScopes;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Settings extends Model
{
    protected $fillable = ['id', 'name', 'label', 'group', 'type', 'payload'];
    public $timestamps = false;
    protected $casts = [
        'payload'=>'json'
    ];

    public static function groups()
    {
        $groups = DB::select("select `group` from settings group by `group`"); //TODO: Find another way
        $groups = array_map(function ($g) {
            return [
                'id' => $g->group,
                'name' => $g->group,
            ];
        }, $groups);
        return $groups;
        User::where('id', 1);
    }

    public static function items($group)
    {
        return Settings::where('group', $group)->get();
    }
}
