<?php

namespace App\Services\Etender;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use JsonException;

class EtenderApiClient
{
	public function getBase(int $eventId): array
	{
		return $this->getJson("/api/events/{$eventId}/base");
	}

	public function getEvent(int $eventId): array
	{
		return $this->getJson("/api/events/{$eventId}");
	}

	public function getInfo(int $eventId): array
	{
		return $this->getJson("/api/events/{$eventId}/info");
	}

	public function getBomLines(int $eventId, int $pageNumber = 1, int $pageSize = 100): array
	{
		return $this->getJson("/api/events/{$eventId}/bomLines?PageSize={$pageSize}&PageNumber={$pageNumber}");
	}

	public function getRevokeHistory(int $eventId): array
	{
		return $this->getJson("/api/events/{$eventId}/revoke-history");
	}

	public function getContactPersons(int $eventId): array
	{
		return $this->getJson("/api/events/{$eventId}/contact-persons");
	}

	public function getAnnouncements(int $eventId): array
	{
		return $this->getJson("/api/events/{$eventId}/announcements");
	}

	private function request(): PendingRequest
	{
		$baseUrl = (string) config('etender.base_url', 'https://etender.gov.az');
		$timeoutSeconds = (int) config('etender.timeout_seconds', 20);

		return Http::baseUrl($baseUrl)
			->acceptJson()
			->timeout($timeoutSeconds)
			->withHeaders([
				'User-Agent' => 'LaravelEtenderParser/1.0',
			]);
	}

	/**
	 * @throws RequestException
	 * @throws JsonException
	 */
	private function getJson(string $path): array
	{
		$response = $this->request()->get($path)->throw();

		$raw = $response->body(); // Always a string

		try {
			$decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
		} catch (JsonException $e) {
			// Short raw snippet for debugging
			$snippet = mb_substr($raw, 0, 800);
			throw new JsonException($e->getMessage() . " | Raw snippet: {$snippet}", $e->getCode(), $e);
		}

		return is_array($decoded) ? $decoded : [];
	}
}
