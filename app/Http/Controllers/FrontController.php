<?php

namespace App\Http\Controllers;

use App\Models\ModelHasPermission;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    protected function checkUser(Request $request)
    {
        try {
            $auth_token =  $request->headers->get('authorization');
            $user = null;
            if (is_string($auth_token) && str_starts_with($auth_token, "Bearer ")) {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken(explode(' ', $auth_token)[1]);
                $user = $token->tokenable;
            }
            return $user;
        } catch (Exception $es) {
            return null;
        }
    }

    public function show(Request $request, string $item)
    {
        $items = config("front.$item");

        $isResource = boolval(strpos($item, "resources-") === 0);
        $isMenu = boolval($item === "menu");
        $user = $this->checkUser($request);
        $permissions = $user == null ? [] : $user->getPermissionNames()->ToArray();

        //check permissions
        if ($user && $user->id != 1) {
            if ($isMenu) {
                $items = array_filter($items, function ($item) use ($permissions) {
                    return in_array("{$item['id']}_menu", $permissions);
                });
            }

            if (!$isResource) { //is mayor resource

            }
        }

        return [
            'data' => [
                'id' => $item,
                'items' => $items
            ]
        ];
    }

    public function index()
    {
        $data = config("front");

        return [
            'data' => [
                'id' => 'front',
                // 'items'=>
            ]
        ];
    }
}
