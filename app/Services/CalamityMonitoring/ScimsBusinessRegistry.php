<?php

namespace App\Services\CalamityMonitoring;

use App\Services\ScimsJuridicalApiService;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ScimsBusinessRegistry extends ScimsJuridicalApiService
{
    protected const ALL_BUSINESSES_CACHE_KEY = 'scims.juridical.businesses';

    protected const ALL_BUSINESSES_STALE_CACHE_KEY = 'scims.juridical.businesses.stale';

    protected const ALL_BUSINESSES_CACHE_TTL_HOURS = 25;

    protected const ALL_BUSINESSES_STALE_CACHE_TTL_DAYS = 7;

    protected const FETCH_BATCH_SIZE = 5;

    protected ?Collection $allBusinesses = null;

    protected bool $usedStaleCache = false;

    /**
     * Fetch every juridical record from SCIMS, cached for local filtering.
     *
     * @return Collection<int, array{entity_no: string, juri_name: string, barangay: ?string, street: ?string, line_of_industry: ?string}>
     */
    public function getAllBusinesses(): Collection
    {
        if ($this->allBusinesses instanceof Collection) {
            return $this->allBusinesses;
        }

        $this->usedStaleCache = false;

        $cached = $this->decodeBusinesses(Cache::get(static::ALL_BUSINESSES_CACHE_KEY));

        if ($cached !== []) {
            return $this->allBusinesses = collect($cached);
        }

        $businesses = $this->fetchAllBusinesses();

        if ($businesses->isNotEmpty()) {
            $payload = $this->encodeBusinesses($businesses->all());

            Cache::put(
                static::ALL_BUSINESSES_CACHE_KEY,
                $payload,
                now()->addHours(static::ALL_BUSINESSES_CACHE_TTL_HOURS),
            );

            Cache::put(
                static::ALL_BUSINESSES_STALE_CACHE_KEY,
                $payload,
                now()->addDays(static::ALL_BUSINESSES_STALE_CACHE_TTL_DAYS),
            );

            return $this->allBusinesses = $businesses;
        }

        $stale = $this->decodeBusinesses(Cache::get(static::ALL_BUSINESSES_STALE_CACHE_KEY));

        if ($stale !== []) {
            $this->usedStaleCache = true;

            return $this->allBusinesses = collect($stale);
        }

        return $this->allBusinesses = $businesses;
    }

    /**
     * @return Collection<int, array{entity_no: string, juri_name: string, barangay: ?string, street: ?string, line_of_industry: ?string}>
     */
    public function refreshAllBusinesses(): Collection
    {
        Cache::forget(static::ALL_BUSINESSES_CACHE_KEY);

        $this->allBusinesses = null;

        return $this->getAllBusinesses();
    }

    public function usedStaleCache(): bool
    {
        return $this->usedStaleCache;
    }

    /**
     * Compress the registry so it fits within small MySQL `max_allowed_packet` limits.
     *
     * @param  array<int, array<string, mixed>>  $businesses
     */
    protected function encodeBusinesses(array $businesses): string
    {
        return base64_encode(gzcompress(serialize($businesses), 6));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function decodeBusinesses(mixed $payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (! is_string($payload) || $payload === '') {
            return [];
        }

        $compressed = base64_decode($payload, true);

        if ($compressed === false) {
            return [];
        }

        $serialized = @gzuncompress($compressed);

        if ($serialized === false) {
            return [];
        }

        $businesses = @unserialize($serialized);

        return is_array($businesses) ? $businesses : [];
    }

    /**
     * @return Collection<int, array{entity_no: string, juri_name: string, barangay: ?string, street: ?string, line_of_industry: ?string}>
     */
    protected function fetchAllBusinesses(): Collection
    {
        $parameters = [
            'per_page' => 100,
            'include' => 'address',
            'expand' => 'details',
        ];

        try {
            $first = Http::baseUrl(config('scims.base_url'))
                ->timeout(30)
                ->get('juridical', ['page' => 1, ...$parameters]);

            if ($first->failed()) {
                return collect();
            }

            $lastPage = (int) $first->json('meta.last_page', 1);

            $businesses = $this->normalizeBusinesses($first->json('data', []));

            for ($start = 2; $start <= $lastPage; $start += static::FETCH_BATCH_SIZE) {
                $responses = Http::pool(function (Pool $pool) use ($start, $lastPage, $parameters): void {
                    $end = min($lastPage, $start + static::FETCH_BATCH_SIZE - 1);

                    foreach (range($start, $end) as $page) {
                        $pool->as((string) $page)
                            ->baseUrl(config('scims.base_url'))
                            ->timeout(30)
                            ->get('juridical', ['page' => $page, ...$parameters]);
                    }
                });

                foreach ($responses as $response) {
                    if ($response instanceof Response && $response->successful()) {
                        $businesses = [...$businesses, ...$this->normalizeBusinesses($response->json('data', []))];
                    }
                }
            }
        } catch (\Throwable) {
            return collect();
        }

        return collect($businesses)->unique('entity_no')->values();
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, array{entity_no: string, juri_name: string, barangay: ?string, street: ?string, line_of_industry: ?string}>
     */
    protected function normalizeBusinesses(array $items): array
    {
        return collect($items)
            ->filter(fn (mixed $item): bool => is_array($item) && filled($item['entity_no'] ?? null))
            ->map(fn (array $item): array => [
                'entity_no' => $item['entity_no'],
                'juri_name' => $item['juri_name'] ?? $item['entity_no'],
                'barangay' => $item['barangay'] ?? null,
                'street' => $item['street'] ?? null,
                'line_of_industry' => $item['line_of_industry'] ?? null,
            ])
            ->values()
            ->all();
    }
}
