<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('tenders', function (Blueprint $table): void {
			$table->id();

			$table->unsignedBigInteger('event_id')->unique(); // Public API key
			$table->unsignedBigInteger('rfx_id')->nullable();
			$table->unsignedBigInteger('inner_event_id')->nullable();

			$table->string('title');
			$table->string('organization_name')->nullable();
			$table->string('organization_voen', 64)->nullable();
			$table->string('address')->nullable();

			$table->string('document_number')->nullable();
			$table->unsignedInteger('document_version')->nullable();

			$table->string('event_type_code', 64)->nullable();
			$table->string('event_status_code', 64)->nullable();
			$table->string('document_view_type_code', 64)->nullable();

			$table->decimal('estimated_amount', 16, 4)->nullable();
			$table->unsignedInteger('min_number_of_suppliers')->nullable();

			$table->timestamp('published_at')->nullable();
			$table->timestamp('start_at')->nullable();
			$table->timestamp('end_at')->nullable();
			$table->timestamp('envelope_at')->nullable();

			$table->decimal('view_fee', 16, 4)->nullable();
			$table->decimal('participation_fee', 16, 4)->nullable();

			$table->json('raw')->nullable(); // Full raw payloads for traceability
			$table->timestamps();

			$table->index(['published_at']);
			$table->index(['event_type_code']);
			$table->index(['event_status_code']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('tenders');
	}
};
