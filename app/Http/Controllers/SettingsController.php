<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{


    public function groups(Request $request)
    {
        return [
            "data" => Settings::groups(),
        ];
    }

    public function items(string $group, Request $request)
    {
        $items = Settings::items($group)->toArray();
        $items = array_reduce($items, function ($prev, $current) {
            $prev[$current['name']] = $current;
            return $prev;
        }, []);
        return [
            "data" => [
                "id" => $group,
                ...$items],
        ];
    }

    public function store_items($group, Request $request)
    {
        $all_values = $request->all();
        $keys = array_keys($all_values);
        $r = [];
        foreach ($keys as $key) {
            if ($key == 'id') {
                continue;
            }

            $setting = Settings::find($all_values[$key]['id']);
            $setting->fill($all_values[$key]);
            $setting->save();
        }
        return $this->items($group, $request);
    }
}
