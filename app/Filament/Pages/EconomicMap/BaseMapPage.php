<?php

namespace App\Filament\Pages\EconomicMap;

use App\Jobs\SyncEconomicMapData;
use App\Services\EconomicMapService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\On;

/**
 * Shared behaviour for the Economic Map pages: sidebar grouping,
 * area search, and data refresh.
 */
abstract class BaseMapPage extends Page
{
    protected Width|string|null $maxContentWidth = Width::Full;

    public string $searchQuery = '';

    public array $searchResults = [];

    public ?array $summary = null;

    public array $pins = [];

    /** Every exact set-location pin rendered on page load. */
    public array $mapPins = [];

    public string $selectedBarangay = '';

    public int $unsetCount = 0;

    public static function getNavigationGroup(): ?string
    {
        return 'Economic Map';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refreshData')
                ->label('Refresh data')
                ->icon(Heroicon::ArrowPath)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Refresh map data?')
                ->modalDescription('Fetches the latest business registry from SCIMS and rebuilds every figure on this page.')
                ->action(function (): void {
                    SyncEconomicMapData::dispatch();

                    Notification::make()
                        ->title('SCIMS sync queued')
                        ->body('The map will use the new snapshot when the background sync finishes.')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function updatedSearchQuery(): void
    {
        $service = app(EconomicMapService::class);

        $this->searchResults = $service->searchArea($this->searchQuery, $this->mapRows($service));
    }

    public function clearSearch(): void
    {
        $this->reset('searchQuery', 'searchResults');
    }

    public function selectBarangay(string $barangay): void
    {
        if (trim($barangay) === '') {
            $this->clearSelection();

            return;
        }

        $service = app(EconomicMapService::class);
        $rows = $this->mapRows($service);

        $this->summary = $service->areaSummary($barangay, null, $rows);
        $this->pins = $service->barangayBusinesses($barangay, $rows);
        $this->clearSearch();

        $this->dispatch('emap-focus', [
            'barangay' => $barangay,
            'pins' => $this->pins,
        ]);
    }

    public function selectSearchResult(int $index): void
    {
        $match = $this->searchResults[$index] ?? null;

        if (! $match) {
            return;
        }

        if ($match['type'] === 'barangay') {
            $this->selectBarangay($match['barangay']);

            return;
        }

        $service = app(EconomicMapService::class);
        $rows = $this->mapRows($service);

        $this->summary = $service->areaSummary($match['barangay'], $match['street'], $rows);
        $this->pins = $service->barangayBusinesses($match['barangay'], $rows);
        $this->clearSearch();

        $this->dispatch('emap-focus-street', [
            'lat' => $match['lat'],
            'lng' => $match['lng'],
            'pins' => $this->pins,
        ]);
    }

    public function clearSelection(): void
    {
        $this->reset('summary', 'pins', 'selectedBarangay');
        $this->dispatch('emap-clear-pins');
    }

    public function updatedSelectedBarangay(): void
    {
        if (trim($this->selectedBarangay) === '') {
            $this->clearSelection();

            return;
        }

        $this->selectBarangay($this->selectedBarangay);
    }

    /** Called from map circle clicks via window.Livewire.dispatch(). */
    #[On('emap-select-barangay')]
    public function selectBarangayFromMap(string $barangay): void
    {
        $this->selectBarangay($barangay);
    }

    /**
     * Rows this page should operate on. Map pages that show only located
     * businesses override onlyLocated(); every other page keeps all rows.
     *
     * @return array<int, array>|null
     */
    protected function mapRows(EconomicMapService $service): ?array
    {
        return $this->onlyLocated() ? $service->locatedBusinessRows() : null;
    }

    /** Whether this page only considers businesses with a set location. */
    protected function onlyLocated(): bool
    {
        return false;
    }

    /**
     * Load the exact set-location pins every map view renders on load.
     * Coincident pins receive display-only offsets so none are hidden.
     */
    protected function loadMapPins(EconomicMapService $service): void
    {
        $this->mapPins = $service->displayPins($service->locatedPins());
    }

    /**
     * Number of SCIMS businesses whose entity number has no `locations` row.
     *
     * Only map pages that render the unset pill call this; the Business
     * Locations table re-renders on pagination and does not pay for the scan.
     */
    protected function loadUnsetCount(): void
    {
        $this->unsetCount = app(EconomicMapService::class)->unsetCount();
    }

    abstract protected function loadData(): void;
}
