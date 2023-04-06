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

            if ($resource == null || !isset($resource['fields'])) {
                continue;
            }
            $maps = array_map(fn ($f) => "{$domain}_{$area}_field-{$f['id']}", $resource['fields']);
            $permissions = array_merge($permissions,$maps);


        }

        $created = 0;

        foreach ($permissions as $rp) {
            if (Permission::where('name', $rp)->count() == 0) {
                $p = new Permission(
                    [
                        'name' => $rp,
                        'guard' => 'web'
                    ]
                );
                $p->save();
                $created++;
            }
        }

        $this->info("Created {$created} permissions");
    }
}
