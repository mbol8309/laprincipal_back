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
class ValidationRule extends Model
{
	protected $table = 'sys_validation_rule';

	protected $fillable = [
		'model_name',
		'field_name',
		'rule_name',
		'rule_parameters'
	];
}
