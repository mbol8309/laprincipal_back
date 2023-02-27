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
}
