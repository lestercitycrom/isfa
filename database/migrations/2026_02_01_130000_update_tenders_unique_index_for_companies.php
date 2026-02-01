<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('tenders', function (Blueprint $table): void {
			$table->dropUnique('tenders_event_id_unique');
			$table->unique(['event_id', 'company_id']);
			$table->index('event_id');
		});
	}

	public function down(): void
	{
		Schema::table('tenders', function (Blueprint $table): void {
			$table->dropUnique(['event_id', 'company_id']);
			$table->dropIndex(['event_id']);
			$table->unique('event_id');
		});
	}
};
