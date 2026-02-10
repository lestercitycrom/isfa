<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('suppliers', function (Blueprint $table): void {
			$table->text('payment_requisites')->nullable()->after('payment_routing_number');
		});
	}

	public function down(): void
	{
		Schema::table('suppliers', function (Blueprint $table): void {
			$table->dropColumn('payment_requisites');
		});
	}
};
