<?php

namespace App\Filament\Resources\Training\TrainingPrograms\Schemas;

use App\Enums\TrainingStatus;
use App\Models\Training\Training;
use App\Services\Training\ScimsTrainingService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Throwable;

class TrainingProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Business & program')
                    ->description('Link the training to a registered business, then describe what was delivered.')
                    ->icon(Heroicon::BuildingOffice2)
                    ->columns(2)
                    ->schema([
                        // entity_no comes from SCIMS and is stored standalone
                        // (there is no FK to a local businesses table).
                        Select::make('entity_no')
                            ->label('Business (Entity No.)')
                            ->required()
                            ->searchable()
                            ->live()
                            ->getSearchResultsUsing(
                                fn (string $search): array => app(ScimsTrainingService::class)->businessOptions($search)
                            )
                            ->getOptionLabelUsing(
                                fn (string $value): string => app(ScimsTrainingService::class)->businessLabel($value)
                            )
                            ->columnSpanFull(),

                        Placeholder::make('business_details')
                            ->label('Business details')
                            ->content(fn (Get $get): Htmlable => self::businessDetailsHtml($get('entity_no')))
                            ->columnSpanFull(),

                        TextInput::make('training_program')
                            ->label('Training Program')
                            ->required()
                            ->maxLength(300)
                            ->placeholder('e.g. BASIC COMPUTER OPERATIONS & CONCEPTS (96 HRS)')
                            ->datalist(fn (): array => self::distinctSuggestions('training_program'))
                            ->columnSpanFull(),

                        TextInput::make('training_type')
                            ->label('Training Type')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('e.g. Digital Literacy Course')
                            ->datalist(fn (): array => self::distinctSuggestions('training_type')),

                        TextInput::make('sector')
                            ->label('Sector')
                            ->maxLength(100)
                            ->placeholder('e.g. Others')
                            ->datalist(fn (): array => self::distinctSuggestions('sector')),

                        TextInput::make('conducted_by')
                            ->label('Conducted By')
                            ->required()
                            ->maxLength(300)
                            ->placeholder('e.g. San Carlos City Library Tech4ED Center')
                            ->datalist(fn (): array => self::distinctSuggestions('conducted_by'))
                            ->columnSpanFull(),
                    ]),

                Section::make('Schedule')
                    ->description('Training dates and the number of training hours.')
                    ->icon(Heroicon::CalendarDays)
                    ->columns(3)
                    ->schema([
                        DatePicker::make('from_date')
                            ->label('From Date')
                            ->required()
                            ->live(),

                        DatePicker::make('to_date')
                            ->label('To Date')
                            ->required()
                            ->afterOrEqual('from_date')
                            ->live(),

                        TextInput::make('hours_no')
                            ->label('No. of Hours')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->suffix('hrs')
                            ->live(debounce: 500),

                        Placeholder::make('duration')
                            ->label('Duration')
                            ->content(fn (Get $get): string => self::durationSummary($get))
                            ->columnSpanFull(),
                    ]),

                Section::make('Delivery & outcome')
                    ->description('Current status and graduation date. The graduation date is required once a record is completed.')
                    ->icon(Heroicon::CheckBadge)
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(TrainingStatus::class)
                            ->default(TrainingStatus::Completed->value)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (mixed $state, Set $set): void {
                                if (TrainingStatus::fromState($state) !== TrainingStatus::Completed) {
                                    $set('graduated_at', null);
                                }
                            }),

                        DatePicker::make('graduated_at')
                            ->label('Graduated At')
                            ->afterOrEqual('to_date')
                            ->visible(fn (Get $get): bool => TrainingStatus::fromState($get('status')) === TrainingStatus::Completed)
                            ->required(fn (Get $get): bool => TrainingStatus::fromState($get('status')) === TrainingStatus::Completed),
                    ]),
            ]);
    }

    /**
     * Inline business preview so the user can confirm the SCIMS match before
     * saving, without leaving the form.
     */
    protected static function businessDetailsHtml(?string $entityNo): Htmlable
    {
        if (blank($entityNo)) {
            $hint = 'Pick a business above to preview its registered name from SCIMS.';

            return new HtmlString('<p class="text-gray-500 dark:text-gray-400">'.e($hint).'</p>');
        }

        $business = app(ScimsTrainingService::class)->findBusiness($entityNo);

        if (blank($business)) {
            $notice = 'SCIMS details unavailable for ('.$entityNo.') — the entity number will still be saved.';

            return new HtmlString('<p class="text-gray-500 dark:text-gray-400">'.e($notice).'</p>');
        }

        $details = [
            'Business name' => filled($business['juri_name'] ?? null) ? $business['juri_name'] : '—',
            'Entity no.' => $entityNo,
        ];

        $blocks = array_map(
            fn (string $label, string $value): string => sprintf(
                '<div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">%s</p><p class="text-sm">%s</p></div>',
                e($label),
                e($value),
            ),
            array_keys($details),
            $details,
        );

        return new HtmlString(
            '<div class="grid grid-cols-1 gap-2 sm:grid-cols-2">'.implode('', $blocks).'</div>',
        );
    }

    protected static function durationSummary(Get $get): string
    {
        $from = $get('from_date');
        $to = $get('to_date');

        if (blank($from) || blank($to)) {
            return 'Pick both dates to calculate the duration.';
        }

        try {
            $days = (int) Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;

            if ($days < 1) {
                return 'The end date must be on or after the start date.';
            }

            $hours = $get('hours_no');

            return filled($hours)
                ? $days.' day(s) · '.$hours.' training hour(s)'
                : $days.' day(s)';

        } catch (Throwable) {
            return '—';
        }
    }

    /**
     * Existing values for a text field, so data entry reuses prior wording.
     *
     * @return list<string>
     */
    protected static function distinctSuggestions(string $column): array
    {
        return Training::query()
            ->whereNotNull($column)
            ->distinct()
            ->orderBy($column)
            ->limit(100)
            ->pluck($column)
            ->toArray();
    }
}
