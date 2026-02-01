<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::connection(config('activitylog.database_connection'))
			->create(config('activitylog.table_name'), function (Blueprint $table): void {
				$table->bigIncrements('id');
				$table->string('log_name')->nullable();
				$table->text('description');
				$table->nullableMorphs('subject', 'subject');
				$table->string('event')->nullable();
				$table->nullableMorphs('causer', 'causer');
				$table->json('properties')->nullable();
				$table->uuid('batch_uuid')->nullable();
				$table->foreignId('company_id')->nullable()->constrained('users')->nullOnDelete();
				$table->timestamps();

				$table->index('log_name');
				$table->index(['company_id', 'created_at']);
				$table->index(['subject_type', 'subject_id']);
			});
	}

	public function down(): void
	{
		Schema::connection(config('activitylog.database_connection'))
			->dropIfExists(config('activitylog.table_name'));
	}
};
