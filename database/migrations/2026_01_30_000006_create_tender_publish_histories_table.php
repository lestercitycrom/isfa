<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('tender_publish_histories', function (Blueprint $table): void {
			$table->id();

			$table->foreignId('tender_id')->constrained('tenders')->cascadeOnDelete();
			$table->timestamp('published_at')->nullable();

			$table->timestamps();

			$table->index(['published_at']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('tender_publish_histories');
	}
};
