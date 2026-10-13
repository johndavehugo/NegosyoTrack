<?php

/**
 * Local stub for Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget.
 *
 * The original package (leandrocfe/filament-apex-charts) does not yet support
 * Laravel 13 / illuminate/contracts v13. This stub allows the widget classes
 * that extend ApexChartWidget to be autoloaded without crashing. Charts are
 * rendered client-side via the ApexCharts JS already bundled in the app; the
 * PHP class just needs to exist so Filament's widget discovery doesn't throw.
 *
 * Replace this stub with the real package once it ships a Laravel-13-compatible
 * release.
 */

namespace Leandrocfe\FilamentApexCharts\Widgets;

use Filament\Widgets\Widget;

abstract class ApexChartWidget extends Widget
{
    // Widgets that use deferLoading check $this->readyToLoad before querying.
    public bool $readyToLoad = false;

    protected static string $view = 'filament-apex-charts::apex-chart-widget';

    /** Chart options returned by the concrete widget. */
    abstract protected function getOptions(): array;

    public function loadChart(): void
    {
        $this->readyToLoad = true;
    }

    public function getOptions_public(): array
    {
        return $this->readyToLoad ? $this->getOptions() : [];
    }

    /**
     * Render a minimal placeholder view so Filament can display
     * the widget card without crashing on the missing Blade view.
     */
    public function render(): \Illuminate\Contracts\View\View|\Illuminate\View\View
    {
        // If the real package view exists, use it; otherwise render a blank card.
        if (view()->exists('filament-apex-charts::apex-chart-widget')) {
            return view('filament-apex-charts::apex-chart-widget');
        }

        return view('filament::widgets.chart', [
            'chartId' => static::$chartId ?? 'chart-' . class_basename(static::class),
            'heading' => static::$heading ?? '',
            'data'    => [],
        ]);
    }
}
