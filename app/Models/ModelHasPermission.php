<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ModelHasPermissions
 *
 * @property int $permission_id
 * @property string $model_type
 * @property int $model_id
 *
 * @property Permissions $permissions
 *
 * @package App\Models
 */
class ModelHasPermission extends Model
{
	protected $table = 'sys_model_has_permission';
	public $incrementing = false;
public $timestamps = false;

	protected $casts = [
		'permission_id' => 'int',
		'model_id' => 'int'
	];

	public function permissions()
	{
		return $this->belongsTo(Permissions::class, 'permission_id');
	}
}
