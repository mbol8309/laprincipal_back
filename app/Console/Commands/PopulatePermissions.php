<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;

class PopulatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:populate-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates permissions based on resources';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $all_resources = config('front');
        $permissions = [];
        $permissions_crud = ['show', 'edit', 'create', 'delete'];

        foreach ($all_resources as $resource_key => $resource) {
            [$domain, $area] = explode('-', $resource_key);
            if ($area == '_sys_menu') { //es un item del menu. Mandy no me lo cambies mas, jejej
                foreach ($resource as $menu) {
                    $permissions[] = "{$domain}_{$menu['id']}_menu";
                    if (isset($menu['children'])) {
                        $childrens = $menu['children'];
                        foreach ($childrens as $children) {
                            $permissions[] = "{$domain}_{$children['id']}_menu";
                        }
                    }
                }
                continue;
            }

            if ($area == '_sys_resources') { //es un recurso global

                $resources = array_map(function ($resource) {
                    return $resource['id'];
                }, $resource);

                //user resouces permissions
                $resources_permissions = [];
                foreach ($resources as $resource) {
                    foreach ($permissions_crud as $permission) {
                        $permissions[] = "{$domain}_{$resource}_{$permission}";
                    }
                }
                continue;
            }
            if ($area == "_sys_usermenu") continue; //TODO: Not implemented or thinked how and why

            if ($resource == null) {
                continue;
            }

            if (isset($resource['fields'])) {
                $maps = array_map(fn ($f) => "{$domain}_{$area}_field-{$f['id']}", $resource['fields']);
                $maps = array_reduce($maps, function ($prev, $curr) {
                    $prev[] = $curr . "_disable";
                    $prev[] = $curr . "_hide";
                    return $prev;
                }, []);
                $permissions = array_merge($permissions, $maps);
            }
            if (isset($resource['views'])) {
                $actions_maps=[];
                $views = $resource['views'];
                foreach($views as $view){
                    if(isset($view['actions'])){ //actions
                        $scopes = $view['actions']; //now global and local
                        foreach($scopes as $scope_key=>$scope){
                            foreach($scope as $action){
                                $actions_maps[] = "{$domain}_{$area}_action_{$scope_key}_{$action['id']}";
                            }

                        }
                    }

                }
                $permissions=array_merge($permissions, $actions_maps);
            }
        }

        $created = 0;

        foreach ($permissions as $rp) {
            if (Permission::where('name', $rp)->count() == 0) {
                $p = new Permission(
                    [
                        'name' => $rp,
                        'guard_name' => 'web'
                    ]
                );
                $p->save();
                $created++;
            }
        }

        $this->info("Created {$created} permissions");
    }
}
