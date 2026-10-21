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

    public string $selectedBarangay = '';

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
        $this->searchResults = app(EconomicMapService::class)->searchArea($this->searchQuery);
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

        $this->summary = $service->areaSummary($barangay);
        $this->pins = $service->barangayBusinesses($barangay);
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

        $this->summary = $service->areaSummary($match['barangay'], $match['street']);
        $this->pins = $service->barangayBusinesses($match['barangay']);
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

    abstract protected function loadData(): void;
}
