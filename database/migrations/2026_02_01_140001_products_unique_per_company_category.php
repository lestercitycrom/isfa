<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('products', static function (Blueprint $table): void {
			$table->unique(
				['company_id', 'name', 'category_id'],
				'products_company_id_name_category_id_unique'
			);
		});
	}

	public function down(): void
	{
		Schema::table('products', static function (Blueprint $table): void {
			$table->dropUnique('products_company_id_name_category_id_unique');
		});
	}
};
