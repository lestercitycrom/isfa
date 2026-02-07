<?php

declare(strict_types=1);

namespace App\Services\Etender;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class EtenderSearchService
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $detailCache = [];

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

    public function queryNeedsDetail(string $query): bool
    {
        $tokens = array_values(array_filter(explode(' ', $this->normalizeQuery($query)), static fn (string $t): bool => $t !== ''));

        foreach ($tokens as $token) {
            if (preg_match('/\d/', $token) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array<string, mixed>>  $candidates
     * @return array<int, array<string, mixed>>
     */
    public function findMatches(string $query, array $candidates): array
    {
        $query = $this->normalizeQuery($query);
        if ($query === '') {
            return [];
        }

        $matches = [];
        $seen = [];

        // Fast pass: list fields only.
        foreach ($candidates as $item) {
            if (! is_array($item)) {
                continue;
            }

            $text = $this->extractSearchableText($item, false);
            if (! $this->matchesQuery($query, $text)) {
                continue;
            }

            $eventId = $this->extractEventId($item);
            if ($eventId === null || isset($seen[$eventId])) {
                continue;
            }

            $seen[$eventId] = true;
            $matches[] = $item;
        }

        if ($matches !== [] || ! $this->queryNeedsDetail($query)) {
            return $matches;
        }

        // Slow pass: include detail fields for digit-heavy queries.
        $maxDetailChecks = max(1, (int) config('etender.search_max_detail_check', 250));
        $checked = 0;

        foreach ($candidates as $item) {
            if (! is_array($item)) {
                continue;
            }

            if ($checked >= $maxDetailChecks) {
                break;
            }
            $checked++;

            $text = $this->extractSearchableText($item, true);
            if (! $this->matchesQuery($query, $text)) {
                continue;
            }

            $eventId = $this->extractEventId($item);
            if ($eventId === null || isset($seen[$eventId])) {
                continue;
            }

            $seen[$eventId] = true;
            $matches[] = $item;
        }

        return $matches;
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
    public function extractSearchableText(array $item, bool $withDetail = false): string
    {
        $parts = [];

        $listText = $this->extractDisplayTitle($item);
        if ($listText !== '') {
            $parts[] = $listText;
        }

        if (! $withDetail) {
            return trim(implode(' ', $parts));
        }

        $detail = $this->fetchEventDetail($this->extractEventId($item));
        if ($detail !== null) {
            foreach (['tenderName', 'eventName', 'organizationName', 'buyerOrganizationName'] as $key) {
                $value = $detail[$key] ?? null;
                if (is_string($value) && trim($value) !== '') {
                    $parts[] = trim($value);
                }
            }

            $categoryCodes = $detail['categoryCodes'] ?? null;
            if (is_array($categoryCodes)) {
                foreach ($categoryCodes as $code) {
                    $codeValue = trim((string) $code);
                    if ($codeValue !== '') {
                        $parts[] = $codeValue;
                    }
                }
            }
        }

        return trim(implode(' ', $parts));
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
                'Accept-Language' => 'az,en;q=0.8,ru;q=0.7',
            ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchEventDetail(?string $eventId): ?array
    {
        if ($eventId === null || $eventId === '') {
            return null;
        }

        if (isset($this->detailCache[$eventId])) {
            return $this->detailCache[$eventId];
        }

        $response = $this->request()->get('/api/events/'.$eventId);
        if (! $response->ok()) {
            return null;
        }

        $data = $response->json();
        if (! is_array($data)) {
            return null;
        }

        $this->detailCache[$eventId] = $data;

        return $data;
    }
}
