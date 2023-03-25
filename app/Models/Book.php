<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Book
 * 
 * @property int $id
 * @property string $title
 * @property Carbon|null $publication_date
 * @property string|null $publisher
 * @property string|null $description
 * @property int|null $author_id
 * 
 * @property Author|null $author
 * @property Collection|Genre[] $genre
 *
 * @package App\Models
 */
class Book extends Model
{
	protected $table = 'bs_book';
	public $timestamps = false;

	protected $casts = [
		'publication_date' => 'date',
		'author_id' => 'int'
	];

	protected $fillable = [
		'title',
		'publication_date',
		'publisher',
		'description',
		'author_id'
	];

	public function author()
	{
		return $this->belongsTo(Author::class);
	}

	public function genre()
	{
		return $this->belongsToMany(Genre::class, 'bs_book_genre');
	}
}
