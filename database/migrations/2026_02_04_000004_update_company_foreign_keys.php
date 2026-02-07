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
		if (DB::getDriverName() === 'sqlite') {
			return;
		}

		$this->replaceForeignKeyTarget('product_categories', 'company_id', 'companies');
		$this->replaceForeignKeyTarget('products', 'company_id', 'companies');
		$this->replaceForeignKeyTarget('suppliers', 'company_id', 'companies');
		$this->replaceForeignKeyTarget('tenders', 'company_id', 'companies');
		$this->replaceForeignKeyTarget('tender_product', 'company_id', 'companies');
		$this->replaceForeignKeyTarget(config('activitylog.table_name'), 'company_id', 'companies');
	}

	public function down(): void
	{
		if (DB::getDriverName() === 'sqlite') {
			return;
		}

		$this->replaceForeignKeyTarget('product_categories', 'company_id', 'users');
		$this->replaceForeignKeyTarget('products', 'company_id', 'users');
		$this->replaceForeignKeyTarget('suppliers', 'company_id', 'users');
		$this->replaceForeignKeyTarget('tenders', 'company_id', 'users');
		$this->replaceForeignKeyTarget('tender_product', 'company_id', 'users');
		$this->replaceForeignKeyTarget(config('activitylog.table_name'), 'company_id', 'users');
	}

	private function replaceForeignKeyTarget(string $table, string $column, string $targetTable): void
	{
		$defaultConstraintName = "{$table}_{$column}_foreign";

		// On some MySQL hosts information_schema may lag during migration steps.
		// Force-drop the conventional FK name first to avoid duplicate name errors.
		try {
			DB::statement("alter table `{$table}` drop foreign key `{$defaultConstraintName}`");
		} catch (\Throwable) {
			// Ignore when it does not exist.
		}

		$rows = DB::select(
			"select constraint_name, referenced_table_name from information_schema.key_column_usage where table_schema = database() and table_name = ? and column_name = ? and referenced_table_name is not null",
			[$table, $column]
		);

		$referencesTarget = collect($rows)->contains(static fn (object $row): bool => ($row->referenced_table_name ?? null) === $targetTable);

		if ($referencesTarget && count($rows) === 1) {
			return;
		}

		foreach ($rows as $row) {
			if (! isset($row->constraint_name)) {
				continue;
			}

			try {
				DB::statement("alter table `{$table}` drop foreign key `{$row->constraint_name}`");
			} catch (\Throwable) {
				// Ignore race/partial-state issues and continue with a clean add below.
			}
		}

		$stillExists = DB::selectOne(
			"select constraint_name from information_schema.table_constraints where constraint_schema = database() and constraint_name = ? and constraint_type = 'FOREIGN KEY' limit 1",
			[$defaultConstraintName]
		);

		if ($stillExists) {
			return;
		}

		Schema::table($table, function (Blueprint $table) use ($column, $targetTable): void {
			$table->foreign($column)->references('id')->on($targetTable)->nullOnDelete();
		});
	}
};
