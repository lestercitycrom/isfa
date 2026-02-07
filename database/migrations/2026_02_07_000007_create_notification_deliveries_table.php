<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('notification_deliveries', function (Blueprint $table): void {
			$table->id();
			$table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->foreignId('tender_id')->constrained('tenders')->cascadeOnDelete();
			$table->string('template_key', 64);
			$table->string('reminder_type', 8);
			$table->string('recipient_email');
			$table->string('subject');
			$table->text('body');
			$table->timestamp('deadline_at')->nullable();
			$table->timestamp('sent_at')->nullable();
			$table->string('status', 16)->default('queued'); // queued|sent|failed
			$table->text('error_message')->nullable();
			$table->json('meta')->nullable();
			$table->timestamps();

			$table->unique(['user_id', 'tender_id', 'reminder_type'], 'delivery_user_tender_type_uq');
			$table->index(['status', 'created_at'], 'delivery_status_created_idx');
			$table->index(['company_id', 'created_at'], 'delivery_company_created_idx');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('notification_deliveries');
	}
};
