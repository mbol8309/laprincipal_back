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
        $resources = config('front.resources');
        $resources = array_map(function ($resource) {
            return $resource['id'];
        }, $resources);
        $permissions_array = ['show', 'edit', 'create', 'delete'];
        //user resouces permissions
        $resources_permissions = [];
        foreach ($resources as $resource) {
            foreach ($permissions_array as $permission) {
                $resources_permissions[] = "{$resource}_{$permission}";
            }
        }
        //resources field permissions
        $fields = [];
        foreach ($resources as $resource) {
            $all_data = config("front.resources-{$resource}");
            if ($all_data == null || !isset($all_data['fields'])) {
                continue;
            }
            $maps = array_map(fn($f)=>"{$resource}_field-{$f['id']}", $all_data['fields']);
            $fields = array_merge($fields, $maps);
        }


        //menus
        $menus=config("front.menu");
        $menu_permissions=[];
        foreach($menus as $menu){
            $menu_permissions[] = "{$menu['id']}_menu";
            if (isset($menu['children'])){
                $childrens = $menu['children'];
                foreach($childrens as $children){
                    $menu_permissions[] = "{$children['id']}_menu";
                }
            }
        }


        $permissions = array_merge($fields,$resources_permissions,$menu_permissions);

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
