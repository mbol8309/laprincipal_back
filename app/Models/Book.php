<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Book
 * 
 * @property int $id
 * @property string $title
 * @property string|null $genre
 * @property Carbon|null $publication_date
 * @property string|null $publisher
 * @property string|null $description
 * @property int|null $author_id
 * 
 * @property Author|null $author
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
		'genre',
		'publication_date',
		'publisher',
		'description',
		'author_id'
	];

	public function author()
	{
		return $this->belongsTo(Author::class);
	}
}
