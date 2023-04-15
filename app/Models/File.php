<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory, HasTimestamps;
    protected $table = 'sys_file';
    protected $fillable = [
        'name',
        'function',
        'path',
        'type',
        'size',
        'thumbnail_path'
    ];
}
