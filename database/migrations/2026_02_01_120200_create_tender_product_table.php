<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('tender_product', function (Blueprint $table): void {
			$table->id();
			$table->foreignId('tender_id')->constrained('tenders')->cascadeOnDelete();
			$table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
			$table->foreignId('company_id')->nullable()->constrained('users')->nullOnDelete();
			$table->timestamps();

			$table->unique(['tender_id', 'product_id']);
			$table->index(['company_id', 'tender_id']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('tender_product');
	}
};
