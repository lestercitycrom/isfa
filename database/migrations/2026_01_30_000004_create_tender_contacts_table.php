<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('tender_contacts', function (Blueprint $table): void {
			$table->id();

			$table->foreignId('tender_id')->constrained('tenders')->cascadeOnDelete();
			$table->string('full_name')->nullable();
			$table->string('position')->nullable();
			$table->string('contact')->nullable(); // Email or other contact string
			$table->string('phone_number', 64)->nullable();

			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('tender_contacts');
	}
};
