<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (DB::getDriverName() === 'sqlite') {
			return;
		}

		if (!Schema::hasTable('product_categories')) {
			return;
		}

		try {
			DB::statement('ALTER TABLE `product_categories` DROP INDEX `product_categories_name_unique`');
		} catch (\Throwable) {
			// Ignore when the legacy index does not exist.
		}

		$hasCompanyNameIndex = DB::selectOne(
			"select 1 from information_schema.statistics where table_schema = database() and table_name = 'product_categories' and index_name = 'product_categories_company_id_name_unique' limit 1"
		) !== null;

		if (!$hasCompanyNameIndex) {
			Schema::table('product_categories', static function (Blueprint $table): void {
				$table->unique(['company_id', 'name'], 'product_categories_company_id_name_unique');
			});
		}
	}

	public function down(): void
	{
		if (DB::getDriverName() === 'sqlite') {
			return;
		}

		if (!Schema::hasTable('product_categories')) {
			return;
		}

		$hasCompanyNameIndex = DB::selectOne(
			"select 1 from information_schema.statistics where table_schema = database() and table_name = 'product_categories' and index_name = 'product_categories_company_id_name_unique' limit 1"
		) !== null;

		if ($hasCompanyNameIndex) {
			Schema::table('product_categories', static function (Blueprint $table): void {
				$table->dropUnique('product_categories_company_id_name_unique');
			});
		}

		$hasNameIndex = DB::selectOne(
			"select 1 from information_schema.statistics where table_schema = database() and table_name = 'product_categories' and index_name = 'product_categories_name_unique' limit 1"
		) !== null;

		if (!$hasNameIndex) {
			Schema::table('product_categories', static function (Blueprint $table): void {
				$table->unique(['name'], 'product_categories_name_unique');
			});
		}
	}
};
