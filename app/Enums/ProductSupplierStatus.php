<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductSupplierStatus: string
{
	case Primary = 'primary';
	case Reserve = 'reserve';

	public function label(): string
	{
		return match ($this) {
			self::Primary => 'Основной',
			self::Reserve => 'Резервный',
		};
	}
}
