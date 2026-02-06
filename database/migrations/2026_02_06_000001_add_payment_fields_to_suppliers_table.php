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
			$table->string('voen', 64)->nullable()->after('name');
			$table->string('payment_method', 32)->nullable()->after('website');
			$table->string('payment_card_number', 64)->nullable()->after('payment_method');
			$table->string('payment_routing_number', 64)->nullable()->after('payment_card_number');
		});
	}

	public function down(): void
	{
		Schema::table('suppliers', function (Blueprint $table): void {
			$table->dropColumn([
				'voen',
				'payment_method',
				'payment_card_number',
				'payment_routing_number',
			]);
		});
	}
};
