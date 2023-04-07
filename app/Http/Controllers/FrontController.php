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
        [$domain, $config] = explode('-', $item);
        $isMenu = boolval($config === "_sys_menu");
        $isUserMenu = boolval($config === "_sys_usermenu");
        $isResource = boolval($config === "_sys_resources");
        $user = $this->checkUser($request);
        $permissions = $user == null ? [] : $user->getAllPermissions();

        //check permissions
        if ($user && $user->id != 1) {
            if ($isMenu) {
                foreach ($items as $key => $menus) {
                    if (isset($menus['children'])) {
                        $items[$key]['children'] = array_filter($menus['children'], function ($child) use ($permissions, $domain) {
                            return $permissions->where('name', "{$domain}_{$child['id']}_menu")->count() > 0;
                        });
                    }
                }

                $items = array_filter($items, function ($item) use ($permissions, $domain) {
                    return  !isset($item['children']) || isset($item['children']) && count($item['children']) > 0;
                });
            }
            if ($isUserMenu) {
                //something
            }

            if ($isResource) {
                foreach($items as $key=>$resource)
                {
                    $keys = array_keys($resource);
                    $keys = array_filter($keys, function ($field) use ($permissions, $domain, $resource) {
                        return $permissions->where('name', "{$domain}_{$resource['id']}_{$field}")->count() > 0;
                    });
                    $keys=array_merge(['list','id'],$keys);
                    $items[$key] = array_intersect_key($resource,array_flip($keys));
                }
            }

            // if (!$isResource) { //is mayor resource

            // }
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
