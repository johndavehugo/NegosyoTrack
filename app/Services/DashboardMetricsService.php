<?php

namespace App\Services;

use App\Models\Msme\Negosyo;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Aggregates the numbers shown on the MSME dashboard.
 *
 * Registration counts come from the cached SCIMS snapshot owned by the
 * Economic Map, while MSME sizes come from the local `negosyos` table where
 * the database trigger derives the category from capitalization.
 */
class DashboardMetricsService
{
    protected const MONTHS = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
    ];

    public function __construct(protected EconomicMapService $economicMap) {}

    /**
     * New and renewal registrations for each month of the selected year.
     *
     * @return array{year: int, labels: array<int, string>, new_registrations: array<int, int>, renewals: array<int, int>, total: int}
     */
    public function monthlyRegistrations(?int $year = null, ?array $rows = null): array
    {
        $year ??= (int) now()->year;
        $rows ??= $this->economicMap->businessList();

        $newRegistrations = array_fill(0, 12, 0);
        $renewals = array_fill(0, 12, 0);

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $juridical = $row['juridical'] ?? null;

            if (! is_array($juridical)) {
                continue;
            }

            $date = trim((string) ($juridical['registration_date'] ?? ''));

            if (! preg_match('/^(\d{4})-(\d{2})/', $date, $matches)) {
                continue;
            }

            if ((int) $matches[1] !== $year) {
                continue;
            }

            $month = (int) $matches[2] - 1;

            if ($month < 0 || $month > 11) {
                continue;
            }

            $status = strtoupper(trim((string) ($juridical['registration_type'] ?? '')));

            if ($status === 'NEW') {
                $newRegistrations[$month]++;
            } elseif ($status === 'RENEW') {
                $renewals[$month]++;
            }
        }

        return [
            'year' => $year,
            'labels' => static::MONTHS,
            'new_registrations' => $newRegistrations,
            'renewals' => $renewals,
            'total' => array_sum($newRegistrations) + array_sum($renewals),
        ];
    }

    /**
     * Micro, Small, and Medium counts from locally saved capitalization.
     *
     * Null and out-of-range categories are intentionally excluded, so the
     * dashboard never shows a "Large" or "Unset" slice.
     *
     * @return array{labels: array<int, string>, series: array<int, int>, total: int}
     */
    public function msmeCategoryBreakdown(): array
    {
        $counts = [
            'MICRO' => 0,
            'SMALL' => 0,
            'MEDIUM' => 0,
        ];

        try {
            if (Schema::hasTable('negosyos')) {
                $totals = Negosyo::query()
                    ->whereIn('category', array_keys($counts))
                    ->selectRaw('UPPER(category) AS category, COUNT(*) AS total')
                    ->groupByRaw('UPPER(category)')
                    ->pluck('total', 'category');

                foreach ($totals as $category => $total) {
                    $key = strtoupper(trim((string) $category));

                    if (array_key_exists($key, $counts)) {
                        $counts[$key] = (int) $total;
                    }
                }
            }
        } catch (Throwable) {
            // Keep the dashboard usable if the local table is unavailable.
        }

        return [
            'labels' => ['Micro', 'Small', 'Medium'],
            'series' => array_values($counts),
            'total' => array_sum($counts),
        ];
    }
}
