<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();

            $table->index(['company_id', 'name']);
            $table->unique(['company_id', 'slug']);
        });

        Schema::create('supplier_tag', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['supplier_id', 'tag_id']);
        });

        Schema::create('tag_tender', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tender_id')->constrained('tenders')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tender_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_tender');
        Schema::dropIfExists('supplier_tag');
        Schema::dropIfExists('tags');
    }
};
