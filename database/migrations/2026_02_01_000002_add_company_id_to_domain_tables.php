<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('product_categories', function (Blueprint $table): void {
			$table->foreignId('company_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
		});

		Schema::table('products', function (Blueprint $table): void {
			$table->foreignId('company_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
		});

		Schema::table('suppliers', function (Blueprint $table): void {
			$table->foreignId('company_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
		});

		Schema::table('tenders', function (Blueprint $table): void {
			$table->foreignId('company_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
		});
	}

	public function down(): void
	{
		Schema::table('product_categories', function (Blueprint $table): void {
			$table->dropConstrainedForeignId('company_id');
		});

		Schema::table('products', function (Blueprint $table): void {
			$table->dropConstrainedForeignId('company_id');
		});

		Schema::table('suppliers', function (Blueprint $table): void {
			$table->dropConstrainedForeignId('company_id');
		});

		Schema::table('tenders', function (Blueprint $table): void {
			$table->dropConstrainedForeignId('company_id');
		});
	}
};
