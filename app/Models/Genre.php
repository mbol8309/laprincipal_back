<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Genre
 * 
 * @property int $id
 * @property string $genre_name
 * 
 * @property Collection|Book[] $book
 *
 * @package App\Models
 */
class Genre extends Model
{
	protected $table = 'bs_genre';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id' => 'int'
	];

	protected $fillable = [
		'genre_name'
	];

	public function book()
	{
		return $this->belongsToMany(Book::class, 'bs_book_genre');
	}
}
