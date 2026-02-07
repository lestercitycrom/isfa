<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Etender\EtenderSearchService;
use Illuminate\Console\Command;

final class TenderDebugKeywordSearchCommand extends Command
{
    protected $signature = 'tender:debug-keyword {query : Keyword or phrase} {--limit=10 : Number of matched rows to print}';

    protected $description = 'Debug keyword matching against eTender candidates';

    public function handle(EtenderSearchService $searchService): int
    {
        $query = trim((string) $this->argument('query'));
        $limit = max(1, (int) $this->option('limit'));

        if ($query === '') {
            $this->error('Query is required.');

            return self::FAILURE;
        }

        $this->info('Query: '.$query);
        $this->line('Needs detail pass: '.($searchService->queryNeedsDetail($query) ? 'yes' : 'no'));
        $this->line('EventType/EventStatus: '.config('etender.search_event_type').'/'.config('etender.search_event_status'));

        $candidates = $searchService->fetchCandidates();
        $this->line('Candidates: '.count($candidates));
        if (count($candidates) === 0) {
            $this->warn('No candidates returned from API. Check ETENDER_SEARCH_EVENT_TYPE / ETENDER_SEARCH_EVENT_STATUS and network access.');
        }

        $matches = $searchService->findMatches($query, $candidates);
        $this->line('Matched: '.count($matches));

        $this->newLine();
        $this->info('Top matches:');

        $printed = 0;
        foreach ($matches as $item) {
            if (! is_array($item)) {
                continue;
            }

            $eventId = $searchService->extractEventId($item) ?? '-';
            $title = $searchService->extractDisplayTitle($item);
            if ($title === '') {
                $title = '(empty title in list payload)';
            }

            $this->line('['.$eventId.'] '.$title);
            $this->line('    '.$searchService->buildDetailUrl((string) $eventId));

            $printed++;
            if ($printed >= $limit) {
                break;
            }
        }

        return self::SUCCESS;
    }
}
