<?php

use App\Livewire\Admin\ActivityLogs\Index as ActivityLogsIndex;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function seedActivity(array $attributes): void
{
	DB::table((string) config('activitylog.table_name', 'activity_log'))->insert(array_merge([
		'log_name' => 'test',
		'description' => 'test activity',
		'event' => 'updated',
		'company_id' => null,
		'created_at' => now(),
		'updated_at' => now(),
	], $attributes));
}

it('shows all activity logs to admin', function (): void {
	$companyAId = DB::table('companies')->insertGetId([
		'name' => 'Company A',
		'created_at' => now(),
		'updated_at' => now(),
	]);
	$companyBId = DB::table('companies')->insertGetId([
		'name' => 'Company B',
		'created_at' => now(),
		'updated_at' => now(),
	]);
	User::factory()->create(['role' => User::ROLE_COMPANY, 'company_id' => $companyAId]);
	User::factory()->create(['role' => User::ROLE_COMPANY, 'company_id' => $companyBId]);
	$admin = User::factory()->create([
		'role' => User::ROLE_ADMIN,
		'company_id' => null,
	]);

	DB::table((string) config('activitylog.table_name', 'activity_log'))->delete();

	seedActivity([
		'description' => 'admin visible 1',
		'company_id' => $companyAId,
	]);
	seedActivity([
		'description' => 'admin visible 2',
		'company_id' => $companyBId,
	]);
	seedActivity([
		'description' => 'admin visible 3',
		'company_id' => null,
	]);

	$this->actingAs($admin);

	Livewire::test(ActivityLogsIndex::class)
		->assertViewHas('activities', fn ($activities) => $activities->total() === 3);
});

it('shows only current company logs to company users', function (): void {
	$companyAId = DB::table('companies')->insertGetId([
		'name' => 'Company A',
		'created_at' => now(),
		'updated_at' => now(),
	]);
	$companyBId = DB::table('companies')->insertGetId([
		'name' => 'Company B',
		'created_at' => now(),
		'updated_at' => now(),
	]);
	User::factory()->create(['role' => User::ROLE_COMPANY, 'company_id' => $companyAId]);
	User::factory()->create(['role' => User::ROLE_COMPANY, 'company_id' => $companyBId]);
	$companyUser = User::factory()->create([
		'role' => User::ROLE_COMPANY,
		'company_id' => $companyAId,
	]);

	DB::table((string) config('activitylog.table_name', 'activity_log'))->delete();

	seedActivity([
		'description' => 'company a log',
		'company_id' => $companyAId,
	]);
	seedActivity([
		'description' => 'company b log',
		'company_id' => $companyBId,
	]);
	seedActivity([
		'description' => 'unscoped log',
		'company_id' => null,
	]);

	$this->actingAs($companyUser);

	Livewire::test(ActivityLogsIndex::class)
		->assertViewHas('activities', fn ($activities) => $activities->total() === 1);
});

it('shows no logs for company users without company binding', function (): void {
	$companyAId = DB::table('companies')->insertGetId([
		'name' => 'Company A',
		'created_at' => now(),
		'updated_at' => now(),
	]);
	User::factory()->create(['role' => User::ROLE_COMPANY, 'company_id' => $companyAId]);

	seedActivity([
		'description' => 'company a log',
		'company_id' => $companyAId,
	]);
	seedActivity([
		'description' => 'unscoped log',
		'company_id' => null,
	]);

	$userWithoutCompany = User::factory()->create([
		'role' => User::ROLE_COMPANY,
		'company_id' => null,
	]);

	$this->actingAs($userWithoutCompany);

	Livewire::test(ActivityLogsIndex::class)
		->assertViewHas('activities', fn ($activities) => $activities->total() === 0);
});
