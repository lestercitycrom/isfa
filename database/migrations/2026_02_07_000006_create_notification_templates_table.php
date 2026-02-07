<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('notification_templates', function (Blueprint $table): void {
			$table->id();
			$table->string('key', 64)->unique();
			$table->string('name');
			$table->string('subject');
			$table->text('body');
			$table->boolean('is_active')->default(true);
			$table->timestamps();
		});

		$now = now();

		DB::table('notification_templates')->insert([
			[
				'key' => 'tender_reminder_7d',
				'name' => 'Tender reminder: 7 days',
				'subject' => '[Tender] {{tender_code}} - 7 days left',
				'body' => "Tender: {{tender_title}}\nCode: {{tender_code}}\nDeadline: {{deadline_at}}\nDays left: {{days_left}}\nLink: {{tender_url}}",
				'is_active' => true,
				'created_at' => $now,
				'updated_at' => $now,
			],
			[
				'key' => 'tender_reminder_3d',
				'name' => 'Tender reminder: 3 days',
				'subject' => '[Tender] {{tender_code}} - 3 days left',
				'body' => "Tender: {{tender_title}}\nCode: {{tender_code}}\nDeadline: {{deadline_at}}\nDays left: {{days_left}}\nLink: {{tender_url}}",
				'is_active' => true,
				'created_at' => $now,
				'updated_at' => $now,
			],
			[
				'key' => 'tender_reminder_1d',
				'name' => 'Tender reminder: 1 day left',
				'subject' => '[Tender] {{tender_code}} - 1 day left',
				'body' => "Tender: {{tender_title}}\nCode: {{tender_code}}\nDeadline: {{deadline_at}}\nDays left: {{days_left}}\nLink: {{tender_url}}",
				'is_active' => true,
				'created_at' => $now,
				'updated_at' => $now,
			],
		]);
	}

	public function down(): void
	{
		Schema::dropIfExists('notification_templates');
	}
};
