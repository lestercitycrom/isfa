<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		$this->ensureCommentColumn('products');
		$this->ensureCommentColumn('product_categories');
		$this->ensureCommentColumn('suppliers');
		$this->ensureCommentColumn('tenders');
	}

	public function down(): void
	{
		// Intentionally left empty: this migration only repairs missing columns.
	}

	private function ensureCommentColumn(string $table): void
	{
		if (!Schema::hasTable($table) || Schema::hasColumn($table, 'comment')) {
			return;
		}

		Schema::table($table, function (Blueprint $blueprint): void {
			$blueprint->text('comment')->nullable();
		});
	}
};
