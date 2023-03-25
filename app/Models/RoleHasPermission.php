<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RoleHasPermissions
 *
 * @property int $permission_id
 * @property int $role_id
 *
 * @property Permissions $permissions
 * @property Roles $roles
 *
 * @package App\Models
 */
class RoleHasPermission extends Model
{
	protected $table = 'sys_sys_role_has_permission';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'permission_id' => 'int',
		'role_id' => 'int'
	];

	public function permissions()
	{
		return $this->belongsTo(Permissions::class, 'permission_id');
	}

	public function roles()
	{
		return $this->belongsTo(Roles::class, 'role_id');
	}
}
