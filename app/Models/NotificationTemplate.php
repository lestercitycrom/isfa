<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class NotificationTemplate extends Model
{
	protected $fillable = [
		'key',
		'name',
		'subject',
		'body',
		'is_active',
	];

	protected $casts = [
		'is_active' => 'bool',
	];
}
