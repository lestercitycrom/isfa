<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	public function up(): void
	{
		// Change to LONGTEXT to safely store very large specs/descriptions.
		DB::statement('ALTER TABLE `tender_items` MODIFY `description` LONGTEXT NULL');
	}

	public function down(): void
	{
		// Restore the original schema from the provided DB dump (varchar(255)).
		DB::statement('ALTER TABLE `tender_items` MODIFY `description` VARCHAR(255) NULL');
	}
};
