<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('tender_announcements', function (Blueprint $table): void {
			$table->id();

			$table->foreignId('tender_id')->constrained('tenders')->cascadeOnDelete();
			$table->unsignedInteger('announcement_version')->nullable();
			$table->unsignedBigInteger('external_id')->nullable();
			$table->text('text')->nullable();

			$table->timestamps();

			$table->unique(['tender_id', 'external_id']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('tender_announcements');
	}
};
