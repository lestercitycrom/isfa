<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('product_attribute_definitions', function (Blueprint $table): void {
			$table->id();
			$table->foreignId('company_id')->nullable();
			$table->string('code', 64);
			$table->string('label');
			$table->string('field_type', 32)->default('text');
			$table->json('options_json')->nullable();
			$table->unsignedSmallInteger('sort_order')->default(100);
			$table->boolean('is_active')->default(true);
			$table->timestamps();

			$table->foreign('company_id', 'pad_company_fk')->references('id')->on('companies')->nullOnDelete();
			$table->unique(['company_id', 'code'], 'pad_company_code_uq');
			$table->index(['company_id', 'is_active', 'sort_order'], 'pad_company_active_sort_idx');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('product_attribute_definitions');
	}
};
