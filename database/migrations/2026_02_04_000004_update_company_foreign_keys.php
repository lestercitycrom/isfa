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

		Schema::table('product_categories', function (Blueprint $table): void {
			$this->dropForeignKey('product_categories', 'company_id');
			$table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
		});

		Schema::table('products', function (Blueprint $table): void {
			$this->dropForeignKey('products', 'company_id');
			$table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
		});

		Schema::table('suppliers', function (Blueprint $table): void {
			$this->dropForeignKey('suppliers', 'company_id');
			$table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
		});

		Schema::table('tenders', function (Blueprint $table): void {
			$this->dropForeignKey('tenders', 'company_id');
			$table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
		});

		Schema::table('tender_product', function (Blueprint $table): void {
			$this->dropForeignKey('tender_product', 'company_id');
			$table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
		});

		Schema::connection(config('activitylog.database_connection'))
			->table(config('activitylog.table_name'), function (Blueprint $table): void {
				$this->dropForeignKey(config('activitylog.table_name'), 'company_id');
				$table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
			});
	}

	public function down(): void
	{
		if (DB::getDriverName() === 'sqlite') {
			return;
		}

		Schema::table('product_categories', function (Blueprint $table): void {
			$this->dropForeignKey('product_categories', 'company_id');
			$table->foreign('company_id')->references('id')->on('users')->nullOnDelete();
		});

		Schema::table('products', function (Blueprint $table): void {
			$this->dropForeignKey('products', 'company_id');
			$table->foreign('company_id')->references('id')->on('users')->nullOnDelete();
		});

		Schema::table('suppliers', function (Blueprint $table): void {
			$this->dropForeignKey('suppliers', 'company_id');
			$table->foreign('company_id')->references('id')->on('users')->nullOnDelete();
		});

		Schema::table('tenders', function (Blueprint $table): void {
			$this->dropForeignKey('tenders', 'company_id');
			$table->foreign('company_id')->references('id')->on('users')->nullOnDelete();
		});

		Schema::table('tender_product', function (Blueprint $table): void {
			$this->dropForeignKey('tender_product', 'company_id');
			$table->foreign('company_id')->references('id')->on('users')->nullOnDelete();
		});

		Schema::connection(config('activitylog.database_connection'))
			->table(config('activitylog.table_name'), function (Blueprint $table): void {
				$this->dropForeignKey(config('activitylog.table_name'), 'company_id');
				$table->foreign('company_id')->references('id')->on('users')->nullOnDelete();
			});
	}

	private function dropForeignKey(string $table, string $column): void
	{
		$row = DB::selectOne(
			"select constraint_name from information_schema.key_column_usage where table_schema = database() and table_name = ? and column_name = ? and referenced_table_name is not null limit 1",
			[$table, $column]
		);

		if ($row && isset($row->constraint_name)) {
			DB::statement("alter table `{$table}` drop foreign key `{$row->constraint_name}`");
		}
	}
};
