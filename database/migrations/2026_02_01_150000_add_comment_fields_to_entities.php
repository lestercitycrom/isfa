<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (Schema::hasTable('products') && !Schema::hasColumn('products', 'comment')) {
			Schema::table('products', function (Blueprint $table): void {
				$table->text('comment')->nullable()->after('description');
			});
		}

		if (Schema::hasTable('product_categories') && !Schema::hasColumn('product_categories', 'comment')) {
			Schema::table('product_categories', function (Blueprint $table): void {
				$table->text('comment')->nullable()->after('description');
			});
		}

		if (Schema::hasTable('tenders') && !Schema::hasColumn('tenders', 'comment')) {
			Schema::table('tenders', function (Blueprint $table): void {
				$table->text('comment')->nullable()->after('raw');
			});
		}
	}

	public function down(): void
	{
		if (Schema::hasTable('products') && Schema::hasColumn('products', 'comment')) {
			Schema::table('products', function (Blueprint $table): void {
				$table->dropColumn('comment');
			});
		}

		if (Schema::hasTable('product_categories') && Schema::hasColumn('product_categories', 'comment')) {
			Schema::table('product_categories', function (Blueprint $table): void {
				$table->dropColumn('comment');
			});
		}

		if (Schema::hasTable('tenders') && Schema::hasColumn('tenders', 'comment')) {
			Schema::table('tenders', function (Blueprint $table): void {
				$table->dropColumn('comment');
			});
		}
	}
};
