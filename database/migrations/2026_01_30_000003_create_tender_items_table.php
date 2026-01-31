<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('tender_items', function (Blueprint $table): void {
			$table->id();

			$table->foreignId('tender_id')->constrained('tenders')->cascadeOnDelete();
			$table->unsignedBigInteger('external_id')->nullable(); // BOM line id
			$table->string('name')->nullable();
			$table->string('description')->nullable();
			$table->string('unit_of_measure', 64)->nullable();
			$table->decimal('quantity', 16, 4)->nullable();
			$table->string('category_code', 64)->nullable();

			$table->timestamps();

			$table->unique(['tender_id', 'external_id']);
			$table->index(['category_code']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('tender_items');
	}
};
