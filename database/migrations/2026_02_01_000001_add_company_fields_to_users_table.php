<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('users', function (Blueprint $table): void {
			$table->string('role')->default('company')->after('password');
			$table->string('company_name')->nullable()->after('role');
			$table->string('legal_name')->nullable()->after('company_name');
			$table->string('tax_id')->nullable()->after('legal_name');
			$table->string('registration_number')->nullable()->after('tax_id');
			$table->string('contact_name')->nullable()->after('registration_number');
			$table->string('phone')->nullable()->after('contact_name');
			$table->string('address')->nullable()->after('phone');
			$table->string('website')->nullable()->after('address');
			$table->text('notes')->nullable()->after('website');
			$table->text('password_plain')->nullable()->after('notes');
		});
	}

	public function down(): void
	{
		Schema::table('users', function (Blueprint $table): void {
			$table->dropColumn([
				'role',
				'company_name',
				'legal_name',
				'tax_id',
				'registration_number',
				'contact_name',
				'phone',
				'address',
				'website',
				'notes',
				'password_plain',
			]);
		});
	}
};
