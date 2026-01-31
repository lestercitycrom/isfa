<?php

namespace App\Services\Etender;

use App\Models\DictionaryValue;

class EtenderDictionaryService
{
	/**
	 * Register code in dictionary without overwriting admin label.
	 */
	public function touch(string $dictionary, ?string $code, array $meta = []): void
	{
		if ($code === null || $code === '') {
			return;
		}

		$value = DictionaryValue::query()
			->where('dictionary', $dictionary)
			->where('code', $code)
			->first();

		if ($value === null) {
			DictionaryValue::query()->create([
				'dictionary' => $dictionary,
				'code' => $code,
				'label' => null, // Admin will fill later
				'meta' => $meta ?: null,
			]);

			return;
		}

		// Update meta only (label is admin-controlled)
		if (!empty($meta)) {
			$merged = array_merge($value->meta ?? [], $meta);
			$value->forceFill(['meta' => $merged])->save();
		}
	}
}
