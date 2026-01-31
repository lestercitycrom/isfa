<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DictionaryValue extends Model
{
	protected $fillable = [
		'dictionary',
		'code',
		'label',
		'meta',
	];

	protected $casts = [
		'meta' => 'array',
	];
}
