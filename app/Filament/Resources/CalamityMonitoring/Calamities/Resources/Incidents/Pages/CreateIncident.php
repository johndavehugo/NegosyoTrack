<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Pages;

use App\Filament\Resources\CalamityMonitoring\Calamities\CalamityResource;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\IncidentResource;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Schemas\IncidentForm;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Tables\AffectedMsmeTable;
use App\Models\CalamityMonitoring\Incident;
use App\Models\CalamityMonitoring\IncidentDraft;
use App\Services\CalamityMonitoringService;
use App\Services\IncidentDraftService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TableSelect;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateIncident extends CreateRecord
{
    protected static string $resource = IncidentResource::class;

    protected int $createdCount = 0;

    protected int $skippedCount = 0;

    protected float $createdTotal = 0.0;

    /**
     * @var array<int, string>|null
     */
    protected ?array $excludedJuridicalIds = null;

    protected bool $isLoadingDamagesPage = false;

    public int $filterVersion = 0;

    #[Locked]
    public string $draftKey = '';

    #[Locked]
    public int $damagesPage = 1;

    #[Locked]
    public int $damagesPerPage = 25;

    public function mount(): void
    {
        parent::mount();

        $this->draftKey = (string) Str::uuid();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Incident Details')
                    ->description('These details are applied to every affected MSME you select below.')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('date_occurred')
                            ->label('Date of Occurrence')
                            ->required(),

                        Select::make('nature_of_damage')
                            ->label('Nature of Damage')
                            ->options(Incident::natureOptions())
                            ->required(),

                        Select::make('status')
                            ->label('Verification Status')
                            ->options(Incident::statusOptions())
                            ->default('PENDING_VERIFICATION')
                            ->required(),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Affected MSMEs')
                    ->description('Filter the SCIMS registry, click Apply filter to load the table, then select the affected MSMEs and enter the estimated cost and photo evidence for each.')
                    ->columns(12)
                    ->schema([
                        Select::make('barangay_filter')
                            ->label('Barangay')
                            ->options(fn (): array => app(CalamityMonitoringService::class)->barangayOptions())
                            ->placeholder('All barangays')
                            ->searchable()
                            ->dehydrated(false)
                            ->columnSpan(4),

                        TextInput::make('msme_search')
                            ->label('Search')
                            ->placeholder('Business name, street, or industry')
                            ->dehydrated(false)
                            ->columnSpan(5),

                        Select::make('industry_filter')
                            ->label('Line of Industry')
                            ->options(fn (): array => app(CalamityMonitoringService::class)->industryOptions())
                            ->placeholder('All industries')
                            ->searchable()
                            ->dehydrated(false)
                            ->columnSpan(3),

                        Placeholder::make('matching_count')
                            ->hiddenLabel()
                            ->content(fn (Get $get): string => $this->matchingCountLabel($get))
                            ->columnSpan(6),

                        Actions::make([
                            Action::make('applyFilters')
                                ->label('Apply filter')
                                ->icon(Heroicon::Funnel)
                                ->color('primary')
                                ->action(fn () => $this->applyFilters()),

                            Action::make('clearSelection')
                                ->label('Clear selection')
                                ->icon(Heroicon::XCircle)
                                ->color('gray')
                                ->action(fn () => $this->clearSelection()),

                            Action::make('reviewSelection')
                                ->label('Review selected')
                                ->icon(Heroicon::Eye)
                                ->color('gray')
                                ->modalHeading('Review selected MSMEs')
                                ->modalWidth('4xl')
                                ->modalSubmitAction(false)
                                ->modalCancelActionLabel('Close')
                                ->mountUsing(fn () => $this->saveDamagesPage())
                                ->modalContent(fn (): View => view('filament.calamity-monitoring.selected-msmes', [
                                    'damages' => $this->draftDamages(),
                                ])),
                        ])
                            ->alignment(Alignment::End)
                            ->columnSpan(6),

                        TableSelect::make('juridical_ids')
                            ->label('Select affected MSMEs')
                            ->helperText(fn (): string => $this->selectionHelperText())
                            ->multiple()
                            ->tableConfiguration(AffectedMsmeTable::class)
                            ->tableArguments(fn (Get $get): array => [
                                'barangay' => $get('barangay_filter'),
                                'search' => $get('msme_search'),
                                'industry' => $get('industry_filter'),
                                'excluded' => $this->excludedJuridicalIds(),
                            ])
                            ->key(fn (): string => 'juridical_ids.'.$this->filterVersion)
                            ->required()
                            ->live()
                            ->columnSpanFull()
                            ->afterStateUpdated(function (?array $state): void {
                                if ($state === null) {
                                    return;
                                }

                                $ids = array_values(array_unique(array_filter(array_map('strval', $state))));
                                $existing = $this->draftKey === ''
                                    ? []
                                    : $this->drafts()->existingIds($this->draftKey);

                                $sortedIds = $ids;
                                $sortedExisting = $existing;

                                sort($sortedIds);
                                sort($sortedExisting);

                                if ($sortedIds === $sortedExisting) {
                                    return;
                                }

                                $this->saveDamagesPage();
                                $this->syncDamagesSelection($ids);
                                $this->damagesPage = 1;
                                $this->loadDamagesPage($ids);
                            }),

                        Group::make([
                            Grid::make(12)
                                ->schema([
                                    Placeholder::make('damages_page')
                                        ->hiddenLabel()
                                        ->content(fn (): string => $this->damagesPageLabel())
                                        ->columnSpan(6),

                                    Actions::make([
                                        Action::make('previousDamagesPage')
                                            ->label('Previous')
                                            ->icon(Heroicon::ChevronLeft)
                                            ->color('gray')
                                            ->disabled(fn (): bool => $this->damagesPage <= 1)
                                            ->action(fn () => $this->previousDamagesPage()),

                                        Action::make('nextDamagesPage')
                                            ->label('Next')
                                            ->icon(Heroicon::ChevronRight)
                                            ->color('gray')
                                            ->disabled(fn (): bool => ! $this->hasNextDamagesPage())
                                            ->action(fn () => $this->nextDamagesPage()),
                                    ])
                                        ->alignment(Alignment::End)
                                        ->columnSpan(6),
                                ])
                                ->visible(fn (): bool => filled($this->data['juridical_ids'] ?? [])),

                            Grid::make(12)
                                ->schema([
                                    TextInput::make('bulk_cost')
                                        ->label('Apply cost to all')
                                        ->prefix('₱')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(Incident::MAX_ESTIMATED_COST)
                                        ->dehydrated(false)
                                        ->columnSpan(4),

                                    Actions::make([
                                        Action::make('applyCostToAll')
                                            ->label('Apply')
                                            ->icon(Heroicon::Banknotes)
                                            ->action(fn () => $this->applyCostToAll()),
                                    ])
                                        ->columnSpan(3),

                                    Placeholder::make('damage_total')
                                        ->hiddenLabel()
                                        ->content(fn (): string => $this->damageSummary())
                                        ->columnSpan(5),
                                ]),

                            Repeater::make('damages')
                                ->hiddenLabel()
                                ->table([
                                    TableColumn::make('MSME')->width('320px'),
                                    TableColumn::make('Barangay')->width('160px'),
                                    TableColumn::make('Estimated Cost')->width('180px'),
                                    TableColumn::make('Photos')->width('260px'),
                                ])
                                ->compact()
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false)
                                ->schema([
                                    Hidden::make('juridical_id'),

                                    Hidden::make('juridical_name'),

                                    Hidden::make('barangay'),

                                    Placeholder::make('juridical_label')
                                        ->hiddenLabel()
                                        ->content(fn (Get $get): string => $get('juridical_name') ?: ($get('juridical_id') ?? '—')),

                                    Placeholder::make('barangay_label')
                                        ->hiddenLabel()
                                        ->content(fn (Get $get): string => $get('barangay') ?: '—'),

                                    TextInput::make('estimated_cost_of_damages')
                                        ->hiddenLabel()
                                        ->prefix('₱')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(Incident::MAX_ESTIMATED_COST)
                                        ->required()
                                        ->live(onBlur: true),

                                    IncidentForm::photoEvidenceField(compact: true),
                                ])
                                ->extraItemActions([
                                    Action::make('removeMsme')
                                        ->icon(Heroicon::Trash)
                                        ->color('danger')
                                        ->tooltip('Remove this MSME')
                                        ->requiresConfirmation()
                                        ->modalHeading('Remove MSME')
                                        ->modalDescription('This only removes the MSME from the current selection. It does not delete any saved incident.')
                                        ->action(function (array $arguments, Repeater $component): void {
                                            $state = $component->getState();
                                            $juridicalId = (string) ($state[$arguments['item']]['juridical_id'] ?? '');

                                            if ($juridicalId !== '') {
                                                $this->removeMsme($juridicalId);
                                            }
                                        }),
                                ]),
                        ])
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => filled($get('juridical_ids'))),
                    ]),
            ])
            ->columns(1);
    }

    public function applyFilters(): void
    {
        $this->filterVersion++;
    }

    public function clearSelection(): void
    {
        if ($this->draftKey !== '') {
            $this->drafts()->clear($this->draftKey);
        }

        $this->damagesPage = 1;

        $this->form->fill([
            ...$this->data,
            'juridical_ids' => [],
            'damages' => [],
        ]);
    }

    public function applyCostToAll(): void
    {
        $cost = $this->data['bulk_cost'] ?? null;

        if (blank($cost)) {
            Notification::make()
                ->warning()
                ->title('Enter an estimated cost first.')
                ->send();

            return;
        }

        $this->saveDamagesPage();
        $this->drafts()->applyCostToAll($this->draftKey, $cost);
        $this->loadDamagesPage();

        Notification::make()
            ->success()
            ->title('Estimated cost applied to all selected MSMEs.')
            ->send();
    }

    public function removeMsme(string $juridicalId): void
    {
        $currentIds = array_values(array_map('strval', $this->data['juridical_ids'] ?? []));

        $this->saveDamagesPage();
        $this->syncDamagesSelection($currentIds);
        $this->drafts()->deleteRow($this->draftKey, $juridicalId);

        $ids = collect($currentIds)
            ->reject(fn (string $id): bool => $id === $juridicalId)
            ->values()
            ->all();

        $this->damagesPage = 1;
        $this->loadDamagesPage($ids);
    }

    public function previousDamagesPage(): void
    {
        $this->saveDamagesPage();
        $this->damagesPage = max(1, $this->damagesPage - 1);
        $this->loadDamagesPage();
    }

    public function nextDamagesPage(): void
    {
        $this->saveDamagesPage();
        $this->damagesPage = min($this->damagesPageCount(), $this->damagesPage + 1);
        $this->loadDamagesPage();
    }

    protected function handleRecordCreation(array $data): Model
    {
        $calamity = $this->getParentRecord();
        $ids = array_values(array_unique(array_filter(array_map('strval', $data['juridical_ids'] ?? []))));

        if ($ids === []) {
            throw ValidationException::withMessages([
                'data.juridical_ids' => 'Select at least one affected MSME.',
            ]);
        }

        $this->saveDamagesPage($data['damages'] ?? [], $ids);
        $this->syncDamagesSelection($ids);

        $damages = $this->drafts()->all($this->draftKey)
            ->map(fn (IncidentDraft $row): array => [
                'juridical_id' => (string) $row->juridical_id,
                'juridical_name' => $row->juridical_name,
                'barangay' => $row->barangay,
                'estimated_cost_of_damages' => $row->estimated_cost_of_damages,
                'photos' => $row->photos ?? [],
            ])
            ->all();

        if ($damages === []) {
            throw ValidationException::withMessages([
                'data.juridical_ids' => 'Select at least one affected MSME.',
            ]);
        }

        $missingCosts = collect($damages)
            ->filter(fn (array $damage): bool => blank($damage['estimated_cost_of_damages']))
            ->map(fn (array $damage): string => $damage['juridical_name'] ?: $damage['juridical_id'])
            ->values()
            ->all();

        if ($missingCosts !== []) {
            Notification::make()
                ->danger()
                ->title('Estimated cost required')
                ->body('Enter an estimated cost for: '.implode(', ', $missingCosts))
                ->send();

            throw ValidationException::withMessages([
                'data.damages' => 'Enter an estimated cost for every selected MSME.',
            ]);
        }

        $shared = [
            'date_occurred' => $data['date_occurred'],
            'nature_of_damage' => $data['nature_of_damage'],
            'status' => $data['status'],
            'remarks' => $data['remarks'] ?? null,
        ];

        $last = null;

        DB::transaction(function () use ($calamity, $damages, $shared, &$last): void {
            foreach ($damages as $damage) {
                $juridicalId = (string) $damage['juridical_id'];
                $photos = array_values($damage['photos'] ?? []);
                $name = $damage['juridical_name'] ?: $juridicalId;

                $incident = $calamity->incident()->firstOrCreate(
                    ['juridical_id' => $juridicalId],
                    [
                        ...$shared,
                        'juridical_name' => $name,
                        'barangay' => $damage['barangay'] ?? null,
                        'estimated_cost_of_damages' => $damage['estimated_cost_of_damages'],
                        'photos' => $photos,
                    ],
                );

                if (! $incident->wasRecentlyCreated) {
                    $this->skippedCount++;

                    if ($photos !== []) {
                        Storage::disk('public')->delete($photos);
                    }

                    continue;
                }

                $this->createdCount++;
                $this->createdTotal += (float) $damage['estimated_cost_of_damages'];
                $last = $incident;
            }

            if ($last !== null) {
                $this->drafts()->forget($this->draftKey);
            }
        });

        if ($last === null) {
            throw ValidationException::withMessages([
                'data.juridical_ids' => 'All selected MSMEs are already recorded for this calamity.',
            ]);
        }

        return $last;
    }

    protected function getCreatedNotification(): ?Notification
    {
        $title = $this->createdCount === 1
            ? '1 incident created'
            : "{$this->createdCount} incidents created";

        $body = 'Total estimated damage: ₱'.number_format($this->createdTotal, 2).'.';

        if ($this->skippedCount > 0) {
            $body .= " {$this->skippedCount} MSME(s) were skipped because they are already recorded for this calamity.";
        }

        return Notification::make()
            ->success()
            ->title($title)
            ->body($body)
            ->actions([
                Action::make('viewIncidents')
                    ->label('View incidents')
                    ->url($this->getRedirectUrl())
                    ->button(),
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return CalamityResource::getUrl('manage-incidents', [
            'record' => $this->getParentRecord(),
        ]);
    }

    protected function drafts(): IncidentDraftService
    {
        return app(IncidentDraftService::class);
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $rows
     * @param  array<int, string>|null  $selectedIds
     */
    protected function saveDamagesPage(?array $rows = null, ?array $selectedIds = null): void
    {
        if ($this->isLoadingDamagesPage || $this->draftKey === '') {
            return;
        }

        $selectedIds ??= $this->data['juridical_ids'] ?? [];
        $selectedIds = array_values(array_unique(array_filter(array_map('strval', $selectedIds))));

        if ($selectedIds === []) {
            return;
        }

        $rows ??= $this->currentDamagesRows();

        $existing = $this->drafts()->findMany(
            $this->draftKey,
            collect($rows)->pluck('juridical_id')->filter()->all(),
        );

        [$rows, $converted] = $this->storeDraftPhotos($rows, $existing);

        $this->drafts()->savePage($this->draftKey, $rows, $selectedIds);

        if ($converted) {
            $this->isLoadingDamagesPage = true;

            $this->form->fill([
                ...$this->data,
                'damages' => $rows,
            ]);

            $this->isLoadingDamagesPage = false;
        }
    }

    /**
     * @param  array<int, string>  $ids
     */
    protected function syncDamagesSelection(array $ids): void
    {
        $ids = array_values(array_unique(array_filter(array_map('strval', $ids))));

        if ($ids === []) {
            if ($this->draftKey !== '') {
                $this->drafts()->clear($this->draftKey);
            }

            return;
        }

        $existing = $this->drafts()->existingIds($this->draftKey);
        $missing = array_values(array_diff($ids, $existing));
        $snapshots = $missing === []
            ? []
            : app(CalamityMonitoringService::class)->snapshotEstablishments($missing);

        $this->drafts()->syncSelection(
            $this->draftKey,
            auth()->id(),
            (int) $this->getParentRecord()->getKey(),
            $ids,
            $snapshots,
        );
    }

    /**
     * @param  array<int, string>|null  $selectedIds
     */
    protected function loadDamagesPage(?array $selectedIds = null): void
    {
        $selectedIds ??= $this->data['juridical_ids'] ?? [];
        $selectedIds = array_values(array_unique(array_filter(array_map('strval', $selectedIds))));

        if ($selectedIds === []) {
            $this->damagesPage = 1;
            $this->form->fill([
                ...$this->data,
                'juridical_ids' => [],
                'damages' => [],
            ]);

            return;
        }

        $this->damagesPage = min(max(1, $this->damagesPage), $this->damagesPageCount($selectedIds));

        $paginator = $this->drafts()->page($this->draftKey, $this->damagesPage, $this->damagesPerPage);

        $this->isLoadingDamagesPage = true;

        $this->form->fill([
            ...$this->data,
            'juridical_ids' => $selectedIds,
            'damages' => $paginator->items(),
        ]);

        $this->isLoadingDamagesPage = false;
    }

    /**
     * @param  array<int, string>|null  $ids
     */
    protected function damagesPageCount(?array $ids = null): int
    {
        $count = count($ids ?? $this->data['juridical_ids'] ?? []);

        return max(1, (int) ceil($count / max(1, $this->damagesPerPage)));
    }

    protected function damagesPageLabel(): string
    {
        $count = count($this->data['juridical_ids'] ?? []);

        if ($count === 0) {
            return 'No MSMEs selected.';
        }

        $from = (($this->damagesPage - 1) * $this->damagesPerPage) + 1;
        $to = min($count, $this->damagesPage * $this->damagesPerPage);

        return sprintf(
            'Showing %d–%d of %d selected MSME(s) • Page %d of %d',
            $from,
            $to,
            $count,
            $this->damagesPage,
            $this->damagesPageCount(),
        );
    }

    protected function hasNextDamagesPage(): bool
    {
        return $this->damagesPage < $this->damagesPageCount();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function draftDamages(): array
    {
        if ($this->draftKey === '') {
            return [];
        }

        return $this->drafts()->all($this->draftKey)
            ->map(fn (IncidentDraft $row): array => [
                'juridical_id' => (string) $row->juridical_id,
                'juridical_name' => $row->juridical_name,
                'barangay' => $row->barangay,
                'estimated_cost_of_damages' => $row->estimated_cost_of_damages,
                'photos' => $row->photos ?? [],
            ])
            ->all();
    }

    protected function selectionHelperText(): string
    {
        $count = count($this->data['juridical_ids'] ?? []);
        $label = $count === 1 ? '1 MSME selected.' : "{$count} MSMEs selected.";

        return "{$label} Selections persist when you change filters. MSMEs already recorded for this calamity are hidden.";
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function currentDamagesRows(): array
    {
        $repeater = $this->form->getComponentByStatePath('data.damages');

        if (! $repeater instanceof Repeater) {
            return $this->data['damages'] ?? [];
        }

        $rows = [];

        foreach ($repeater->getItems() as $key => $item) {
            $row = $repeater->getRawItemState($key);

            $photos = $item->getComponent('photos');

            if ($photos instanceof FileUpload) {
                $row['photos'] = $photos->getRawState() ?? [];
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  Collection<string, IncidentDraft>  $existing
     * @return array{0: array<int, array<string, mixed>>, 1: bool}
     */
    protected function storeDraftPhotos(array $rows, Collection $existing): array
    {
        $converted = false;

        $rows = collect($rows)
            ->filter(fn (mixed $row): bool => is_array($row))
            ->map(function (array $row) use (&$converted, $existing): array {
                $juridicalId = (string) ($row['juridical_id'] ?? '');
                $draft = $existing->get($juridicalId);
                $missingTemporaryPhoto = false;

                $row['photos'] = collect($row['photos'] ?? [])
                    ->map(function (mixed $photo) use (&$converted, &$missingTemporaryPhoto): ?string {
                        if ($photo instanceof TemporaryUploadedFile) {
                            if (! $photo->exists()) {
                                $missingTemporaryPhoto = true;
                                $converted = true;

                                return null;
                            }

                            $stored = Storage::disk('public')->putFile('calamity-incidents/photos', $photo);

                            $photo->delete();

                            $converted = true;

                            return $stored ?: null;
                        }

                        return is_string($photo) && $photo !== '' ? $photo : null;
                    })
                    ->filter()
                    ->values()
                    ->all();

                if (
                    $missingTemporaryPhoto
                    && $draft instanceof IncidentDraft
                    && filled($draft->photos)
                ) {
                    $row['photos'] = $draft->photos;
                }

                return $row;
            })
            ->values()
            ->all();

        return [$rows, $converted];
    }

    protected function matchingCountLabel(Get $get): string
    {
        $count = app(CalamityMonitoringService::class)->countEstablishments(
            barangay: $get('barangay_filter'),
            search: $get('msme_search'),
            industry: $get('industry_filter'),
            excluded: $this->excludedJuridicalIds(),
        );

        return $count === 1
            ? '1 MSME matches the current filters.'
            : "{$count} MSMEs match the current filters.";
    }

    protected function damageSummary(): string
    {
        $summary = $this->draftKey === ''
            ? ['count' => 0, 'total' => 0.0, 'with_photos' => 0]
            : $this->drafts()->summary($this->draftKey);

        return sprintf(
            '%d MSME(s) • Total estimated damage: ₱%s • %d with photo evidence',
            $summary['count'],
            number_format($summary['total'], 2),
            $summary['with_photos'],
        );
    }

    /**
     * @return array<int, string>
     */
    protected function excludedJuridicalIds(): array
    {
        return $this->excludedJuridicalIds ??= $this->getParentRecord()
            ->incident()
            ->pluck('juridical_id')
            ->filter()
            ->values()
            ->all();
    }
}
