<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('tender_item_supplier', function (Blueprint $table): void {
			$table->id();
			$table->foreignId('tender_item_id')->constrained('tender_items')->cascadeOnDelete();
			$table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
			$table->string('status')->default('reserve');
			$table->text('terms')->nullable();
			$table->timestamps();

			$table->unique(['tender_item_id', 'supplier_id']);
			$table->index(['status']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('tender_item_supplier');
	}
};
