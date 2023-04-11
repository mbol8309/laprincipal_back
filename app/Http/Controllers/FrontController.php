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
        $isResourceData = boolval(!$isMenu && !$isUserMenu && !$isResource);
        $user = $this->checkUser($request);
        $permissions = $user == null ? [] : $user->getAllPermissions();

        //check permissions
        if ($user && $user->id != 1) {
            if ($isMenu) {
                foreach ($items as $key => $menus) {
                    if (isset($menus['children'])) {
                        $children = array_values(array_filter($menus['children'], function ($child) use ($permissions, $domain) { //array_values is called to reset index to 0
                            return $permissions->where('name', "{$domain}_{$child['id']}_menu")->count() > 0;
                        }));
                        $items[$key]['children'] = $children;
                    }
                }

                $items = array_values(array_filter($items, function ($item) use ($permissions, $domain) {
                    return  !isset($item['children']) || isset($item['children']) && count($item['children']) > 0;
                }));
            }
            if ($isUserMenu) {
                //something
            }

            if ($isResource) {
                foreach($items as $key=>$resource)
                {
                    $keys = array_keys($resource);
                    $keys = array_values(array_filter($keys, function ($field) use ($permissions, $domain, $resource) {
                        return $permissions->where('name', "{$domain}_{$resource['id']}_{$field}")->count() > 0;
                    }));
                    $keys=array_merge(['list','id'],$keys);
                    $items[$key] = array_intersect_key($resource,array_flip($keys));
                }
            }

            if ($isResourceData) { //is mayor resource
                $fields = [];
                if (isset($items['fields'])){
                    $fields = array_values(array_filter($items['fields'],function ($field) use($permissions,$domain,$config){
                        return $permissions->where('name',"{$domain}_{$config}_field-{$field['id']}_hide")->count() == 0;
                    }));
                    foreach($fields as $field_key=>$field){
                        if ($permissions->where('name',"{$domain}_{$config}_field-{$field['id']}_disable")->count() > 0){
                            $fields[$field_key]['disabled']=true;
                        }
                    }
                }
                $items['fields']=$fields;
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
