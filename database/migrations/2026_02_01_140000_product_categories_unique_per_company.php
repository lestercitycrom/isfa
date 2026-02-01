<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('product_categories', static function (Blueprint $table): void {
			$table->dropUnique(['name']);
			$table->unique(['company_id', 'name'], 'product_categories_company_id_name_unique');
		});
	}

	public function down(): void
	{
		Schema::table('product_categories', static function (Blueprint $table): void {
			$table->dropUnique('product_categories_company_id_name_unique');
			$table->unique(['name']);
		});
	}
};
