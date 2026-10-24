<?php

use App\Services\ScimsJuridicalApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

it('does not cache an empty scims registry result', function () {
    Http::fake([
        '*' => Http::response([
            'data' => [],
            'meta' => ['last_page' => 1],
        ]),
    ]);

    $businesses = app(ScimsJuridicalApiService::class)->getAllBusinesses();

    expect($businesses)->toBeEmpty()
        ->and(Cache::has('scims.juridical.businesses'))->toBeFalse()
        ->and(Cache::has('scims.juridical.businesses.stale'))->toBeFalse();
});

it('falls back to the stale registry when a refresh fails', function () {
    $stale = [
        ['entity_no' => 'S1', 'juri_name' => 'Stale Store', 'barangay' => 'Bagonbon', 'street' => 'Proper', 'line_of_industry' => 'Retail'],
    ];

    Cache::put('scims.juridical.businesses.stale', $stale, now()->addDays(7));

    Http::fake([
        '*' => Http::response([], 500),
    ]);

    $service = app(ScimsJuridicalApiService::class);

    $businesses = $service->refreshAllBusinesses();

    expect($businesses->all())->toBe($stale)
        ->and($service->usedStaleCache())->toBeTrue()
        ->and(Cache::has('scims.juridical.businesses'))->toBeFalse();
});

it('uses the configured scims base url', function () {
    config(['scims.base_url' => 'https://scims.example.test/api']);

    Http::fake([
        'https://scims.example.test/api/juridical*' => Http::response([
            'data' => [
                ['entity_no' => 'C1', 'juri_name' => 'Configured Store', 'barangay' => 'Bagonbon'],
            ],
            'meta' => ['last_page' => 1],
        ]),
    ]);

    $businesses = app(ScimsJuridicalApiService::class)->getAllBusinesses();

    expect($businesses)->toHaveCount(1);

    Http::assertSent(fn ($request): bool => str_starts_with(
        $request->url(),
        'https://scims.example.test/api/juridical',
    ));
});
