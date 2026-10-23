<?php

namespace App\Filament\Pages\CalamityMonitoring;

use App\Filament\Resources\CalamityMonitoring\Calamities\CalamityResource;
use App\Filament\Widgets\CalamityDamageByBarangayChartWidget;
use App\Filament\Widgets\CalamityNatureDonutWidget;
use App\Filament\Widgets\CalamityStatusDonutWidget;
use App\Models\CalamityMonitoring\Calamity;
use App\Services\CalamityMonitoring\ReportExporter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Reports extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Reports';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentChartBar;

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Calamity Reports';

    protected static ?string $slug = 'calamity-monitoring/reports';

    protected string $view = 'filament.pages.calamity-monitoring.reports';

    public ?string $calamityId = null;

    public static function getNavigationGroup(): ?string
    {
        return 'Calamity Monitoring';
    }

    public function mount(): void
    {
        $this->calamityId = (string) (Calamity::query()
            ->orderByDesc('declaration_date')
            ->orderByDesc('id')
            ->value('id') ?? '');
    }

    public function getSubheading(): ?string
    {
        return 'Generate printable and spreadsheet damage assessment reports for each calamity.';
    }

    /**
     * @return array<class-string>
     */
    public function getChartWidgets(): array
    {
        return [
            CalamityDamageByBarangayChartWidget::class,
            CalamityStatusDonutWidget::class,
            CalamityNatureDonutWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Calamity::query())
            ->defaultSort('declaration_date', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Calamity')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('calamity_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => str($state ?? 'Unknown')
                        ->replace('_', ' ')
                        ->title()
                        ->toString())
                    ->sortable(),

                TextColumn::make('declaration_date')
                    ->label('Declaration Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('incident_count')
                    ->label('Affected MSMEs')
                    ->counts('incident')
                    ->badge()
                    ->color('warning')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('incident_sum_estimated_cost_of_damages')
                    ->label('Total Damage')
                    ->sum('incident', 'estimated_cost_of_damages')
                    ->formatStateUsing(fn ($state): string => '₱'.number_format((float) $state, 2))
                    ->alignEnd()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('calamity_type')
                    ->label('Calamity Type')
                    ->options([
                        'TYPHOON' => 'Typhoon',
                        'EARTHQUAKE' => 'Earthquake',
                        'LANDSLIDE' => 'Landslide',
                        'FLOOD' => 'Flood',
                    ]),
            ])
            ->recordActions([
                Action::make('viewReport')
                    ->label('View report')
                    ->icon(Heroicon::Printer)
                    ->color('gray')
                    ->url(fn (Calamity $record): string => route('calamity-monitoring.report', [
                        'calamity' => $record,
                    ]))
                    ->openUrlInNewTab(),

                Action::make('exportExcel')
                    ->label('Excel')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('gray')
                    ->action(fn (Calamity $record): BinaryFileResponse => $this->export($record, 'xlsx')),

                Action::make('exportCsv')
                    ->label('CSV')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('gray')
                    ->action(fn (Calamity $record): BinaryFileResponse => $this->export($record, 'csv')),

                Action::make('manageIncidents')
                    ->label('Manage incidents')
                    ->icon(Heroicon::DocumentText)
                    ->url(fn (Calamity $record): string => CalamityResource::getUrl('manage-incidents', [
                        'record' => $record->id,
                    ])),
            ]);
    }

    public function export(Calamity $calamity, string $format): BinaryFileResponse
    {
        return app(ReportExporter::class)->download(
            $calamity,
            $calamity->incident()->getQuery(),
            $format,
        );
    }
}
