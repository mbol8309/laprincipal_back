<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ModelHasRoles
 *
 * @property int $role_id
 * @property string $model_type
 * @property int $model_id
 *
 * @property Roles $roles
 *
 * @package App\Models
 */
class ModelHasRole extends Model
{
	protected $table = 'sys_model_has_role';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'role_id' => 'int',
		'model_id' => 'int'
	];

	public function roles()
	{
		return $this->belongsTo(Roles::class, 'role_id');
	}
}
