<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('products', function (Blueprint $table): void {
			$table->string('color', 64)->nullable()->after('description');
			$table->string('unit', 64)->nullable()->after('color');
			$table->text('characteristics')->nullable()->after('unit');
		});
	}

	public function down(): void
	{
		Schema::table('products', function (Blueprint $table): void {
			$table->dropColumn(['color', 'unit', 'characteristics']);
		});
	}
};
