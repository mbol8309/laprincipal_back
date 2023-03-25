<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Permissions
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|ModelHasPermissions[] $model_has_permissions
 * @property Collection|RoleHasPermissions[] $role_has_permissions
 *
 * @package App\Models
 */
class Permission extends Model
{
	protected $table = 'sys_permission';

	protected $fillable = [
		'name',
		'guard_name'
	];

	public function model_has_permissions()
	{
		return $this->hasMany(ModelHasPermissions::class, 'permission_id');
	}

	public function role_has_permissions()
	{
		return $this->hasMany(RoleHasPermissions::class, 'permission_id');
	}
}
