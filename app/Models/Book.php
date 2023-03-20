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
 * @property string $author
 * @property string|null $genre
 * @property Carbon|null $publication_date
 * @property string|null $publisher
 * @property string|null $description
 *
 * @package App\Models
 */
class Book extends Model
{
	protected $table = 'bs_book';
	public $timestamps = false;

	protected $casts = [
		'publication_date' => 'date'
	];

	protected $fillable = [
		'title',
		'author',
		'genre',
		'publication_date',
		'publisher',
		'description'
	];
}
