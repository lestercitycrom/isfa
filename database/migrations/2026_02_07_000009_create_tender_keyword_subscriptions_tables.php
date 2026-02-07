<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tender_keyword_subscriptions', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('email');
            $table->string('keyword', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'email', 'keyword'], 'tks_company_email_keyword_unique');
            $table->index(['is_active']);
        });

        Schema::create('tender_keyword_deliveries', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('subscription_id')->constrained('tender_keyword_subscriptions')->cascadeOnDelete();
            $table->string('event_id', 64);
            $table->string('event_title')->nullable();
            $table->string('event_url', 1024)->nullable();
            $table->string('recipient_email');
            $table->string('status', 32)->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['subscription_id', 'event_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_keyword_deliveries');
        Schema::dropIfExists('tender_keyword_subscriptions');
    }
};
