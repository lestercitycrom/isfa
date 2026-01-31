<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('dictionary_values', function (Blueprint $table): void {
			$table->id();
			$table->string('dictionary', 64); // Dictionary key (e.g. event_type)
			$table->string('code', 64); // Raw code value (e.g. "7")
			$table->string('label')->nullable(); // Admin-defined label
			$table->json('meta')->nullable(); // Extra context (sample event, etc.)
			$table->timestamps();

			$table->unique(['dictionary', 'code']);
			$table->index(['dictionary']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('dictionary_values');
	}
};
