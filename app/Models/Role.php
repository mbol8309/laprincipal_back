<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Roles
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|ModelHasRoles[] $model_has_roles
 * @property Collection|RoleHasPermissions[] $role_has_permissions
 *
 * @package App\Models
 */
class Role extends Model
{
	protected $table = 'sys_role';

	protected $fillable = [
		'name',
		'guard_name'
	];

	public function model_has_roles()
	{
		return $this->hasMany(ModelHasRoles::class, 'role_id');
	}

	public function role_has_permissions()
	{
		return $this->hasMany(RoleHasPermissions::class, 'role_id');
	}
}
