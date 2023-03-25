<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BookGenre
 * 
 * @property int $book_id
 * @property int $genre_id
 * 
 * @property Book $book
 * @property Genre $genre
 *
 * @package App\Models
 */
class BookGenre extends Model
{
	protected $table = 'bs_book_genre';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'book_id' => 'int',
		'genre_id' => 'int'
	];

	public function book()
	{
		return $this->belongsTo(Book::class);
	}

	public function genre()
	{
		return $this->belongsTo(Genre::class);
	}
}
