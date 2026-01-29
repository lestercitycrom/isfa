<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('product_supplier', static function (Blueprint $table): void {
			$table->id();
			$table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
			$table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();

			$table->string('status')->default('reserve'); // primary|reserve
			$table->text('terms')->nullable(); // price/conditions text

			$table->timestamps();

			$table->unique(['product_id', 'supplier_id']);
			$table->index(['status']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('product_supplier');
	}
};
