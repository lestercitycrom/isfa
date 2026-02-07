<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('product_attribute_values', function (Blueprint $table): void {
			$table->id();
			$table->foreignId('product_id');
			$table->foreignId('product_attribute_definition_id');
			$table->text('value_text')->nullable();
			$table->json('value_json')->nullable();
			$table->timestamps();

			$table->foreign('product_id', 'pav_product_fk')->references('id')->on('products')->cascadeOnDelete();
			$table->foreign('product_attribute_definition_id', 'pav_definition_fk')->references('id')->on('product_attribute_definitions')->cascadeOnDelete();
			$table->unique(['product_id', 'product_attribute_definition_id'], 'pav_product_definition_uq');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('product_attribute_values');
	}
};
