<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (DB::getDriverName() === 'sqlite') {
			return;
		}

		if (!Schema::hasTable('companies') || !Schema::hasColumn('users', 'company_id')) {
			return;
		}

		$companyUsers = DB::table('users')
			->where('role', 'company')
			->get();

		if ($companyUsers->isEmpty()) {
			return;
		}

		$activityTable = config('activitylog.table_name', 'activity_log');

		DB::statement('SET FOREIGN_KEY_CHECKS=0');

		foreach ($companyUsers as $user) {
			$companyId = DB::table('companies')->insertGetId([
				'name' => $user->company_name ?: $user->name ?: ('Company #' . $user->id),
				'legal_name' => $user->legal_name,
				'tax_id' => $user->tax_id,
				'registration_number' => $user->registration_number,
				'contact_name' => $user->contact_name,
				'phone' => $user->phone,
				'address' => $user->address,
				'website' => $user->website,
				'notes' => $user->notes,
				'created_at' => $user->created_at ?? now(),
				'updated_at' => $user->updated_at ?? now(),
			]);

			DB::table('users')
				->where('id', $user->id)
				->update(['company_id' => $companyId]);

			DB::table('product_categories')
				->where('company_id', $user->id)
				->update(['company_id' => $companyId]);
			DB::table('products')
				->where('company_id', $user->id)
				->update(['company_id' => $companyId]);
			DB::table('suppliers')
				->where('company_id', $user->id)
				->update(['company_id' => $companyId]);
			DB::table('tenders')
				->where('company_id', $user->id)
				->update(['company_id' => $companyId]);
			DB::table('tender_product')
				->where('company_id', $user->id)
				->update(['company_id' => $companyId]);

			if (Schema::hasTable($activityTable)) {
				DB::table($activityTable)
					->where('company_id', $user->id)
					->update(['company_id' => $companyId]);
			}
		}

		DB::statement('SET FOREIGN_KEY_CHECKS=1');
	}

	public function down(): void
	{
		// Data migration; no rollback.
	}
};
