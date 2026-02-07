<?php

declare(strict_types=1);

namespace App\Services\Etender;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class EtenderSearchService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchCandidates(): array
    {
        $pages = max(1, (int) config('etender.search_pages', 5));
        $pageSize = max(10, (int) config('etender.search_page_size', 100));

        $collected = [];
        $seen = [];

        for ($page = 1; $page <= $pages; $page++) {
            $items = $this->fetchEventsPage($page, $pageSize);
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $eventId = $this->extractEventId($item);
                if ($eventId === null || isset($seen[$eventId])) {
                    continue;
                }

                $seen[$eventId] = true;

                if (! $this->passesFilters($item)) {
                    continue;
                }

                $collected[] = $item;
            }
        }

        return $collected;
    }

    public function normalizeQuery(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';

        return $value;
    }

    public function matchesQuery(string $query, string $text): bool
    {
        $query = $this->normalizeQuery($query);
        $text = $this->normalizeQuery($text);

        if ($query === '' || $text === '') {
            return false;
        }

        $tokens = array_values(array_filter(explode(' ', $query), static fn (string $t): bool => $t !== ''));
        if ($tokens === []) {
            return false;
        }

        foreach ($tokens as $token) {
            if (! str_contains($text, $token)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public function extractEventId(array $item): ?string
    {
        foreach (['eventId', 'EventId', 'id', 'Id'] as $key) {
            $value = $item[$key] ?? null;
            if ($value === null) {
                continue;
            }

            $eventId = trim((string) $value);
            if ($eventId !== '') {
                return $eventId;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public function extractDisplayTitle(array $item): string
    {
        $parts = [];
        foreach (['eventName', 'EventName', 'buyerOrganizationName', 'BuyerOrganizationName'] as $key) {
            $value = $item[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                $parts[] = trim($value);
            }
        }

        return trim(implode(' ', $parts));
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public function extractSearchableText(array $item): string
    {
        return $this->extractDisplayTitle($item);
    }

    public function buildDetailUrl(string $eventId): string
    {
        $base = rtrim((string) config('etender.base_url', 'https://etender.gov.az'), '/');

        return $base.'/main/competition/detail/'.$eventId;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchEventsPage(int $pageNumber, int $pageSize): array
    {
        $path = '/api/events';

        $response = $this->request()->get($path, [
            'EventType' => (string) config('etender.search_event_type', '0'),
            'EventStatus' => (string) config('etender.search_event_status', '0'),
            'PageNumber' => $pageNumber,
            'PageSize' => $pageSize,
            'buyerOrganizationName' => '',
            'documentNumber' => '',
            'publishDateFrom' => '',
            'publishDateTo' => '',
            'AwardedparticipantName' => '',
            'AwardedparticipantVoen' => '',
            'DocumentViewType' => '',
        ]);

        if (! $response->ok()) {
            return [];
        }

        $data = $response->json();
        if (! is_array($data)) {
            return [];
        }

        foreach (['data', 'Data', 'items', 'Items', 'results', 'Results'] as $key) {
            $value = $data[$key] ?? null;
            if (is_array($value)) {
                return $value;
            }
        }

        foreach ($data as $value) {
            if (is_array($value)) {
                return $value;
            }
        }

        return [];
    }

    private function passesFilters(array $item): bool
    {
        return $this->passesActiveFilter($item) && $this->passesFreshFilter($item);
    }

    private function passesActiveFilter(array $item): bool
    {
        if (! (bool) config('etender.search_active_only', true)) {
            return true;
        }

        $endAt = $this->parseDateTime($item['endDate'] ?? null);
        if ($endAt === null) {
            return true;
        }

        return $endAt->greaterThanOrEqualTo(CarbonImmutable::now('UTC'));
    }

    private function passesFreshFilter(array $item): bool
    {
        $daysBack = (int) config('etender.search_days_back', 30);
        if ($daysBack <= 0) {
            return true;
        }

        $publishedAt = $this->parseDateTime($item['publishDate'] ?? null);
        if ($publishedAt === null) {
            return true;
        }

        return $publishedAt->greaterThanOrEqualTo(CarbonImmutable::now('UTC')->subDays($daysBack));
    }

    private function parseDateTime(mixed $value): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $seconds = (int) $value;

            return $seconds > 0 ? CarbonImmutable::createFromTimestampUTC($seconds) : null;
        }

        if (! is_string($value)) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value, 'UTC');
        } catch (\Throwable) {
            return null;
        }
    }

    private function request(): PendingRequest
    {
        $baseUrl = (string) config('etender.base_url', 'https://etender.gov.az');
        $timeoutSeconds = (int) config('etender.timeout_seconds', 20);

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->timeout($timeoutSeconds)
            ->withHeaders([
                'User-Agent' => 'LaravelEtenderKeywordSearch/1.0',
            ]);
    }
}
