<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Author
 * 
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * 
 * @property Collection|Book[] $books
 *
 * @package App\Models
 */
class Author extends Model
{
	protected $table = 'bs_author';
	public $timestamps = false;

	protected $fillable = [
		'first_name',
		'last_name'
	];

	public function books()
	{
		return $this->hasMany(Book::class);
	}
}
