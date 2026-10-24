<?php

namespace App\Filament\Resources\Training\TrainingPrograms\Schemas;

use App\Models\Training\Training;
use App\Services\Training\ScimsTrainingService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TrainingProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Links the record to a registered business — entity_no comes
                // from SCIMS and is stored standalone (no FK to a local table).
                Select::make('entity_no')
                    ->label('Business (Entity No.)')
                    ->required()
                    ->searchable()
                    ->getSearchResultsUsing(
                        fn (string $search): array => app(ScimsTrainingService::class)->businessOptions($search)
                    )
                    ->getOptionLabelUsing(
                        fn (string $value): string => app(ScimsTrainingService::class)->businessLabel($value)
                    ),

                TextInput::make('training_program')
                    ->label('Training Program')
                    ->required()
                    ->maxLength(300)
                    ->placeholder('e.g. BASIC COMPUTER OPERATIONS & CONCEPTS (96 HRS)')
                    ->datalist(fn (): array => self::distinctSuggestions('training_program'))
                    ->columnSpanFull(),

                DatePicker::make('from_date')
                    ->label('From Date')
                    ->required(),

                DatePicker::make('to_date')
                    ->label('To Date')
                    ->required()
                    ->afterOrEqual('from_date'),

                TextInput::make('hours_no')
                    ->label('No. of Hours')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                TextInput::make('training_type')
                    ->label('Training Type')
                    ->required()
                    ->maxLength(150)
                    ->placeholder('e.g. Digital Literacy Course')
                    ->datalist(fn (): array => self::distinctSuggestions('training_type')),

                TextInput::make('conducted_by')
                    ->label('Conducted By')
                    ->required()
                    ->maxLength(300)
                    ->placeholder('e.g. San Carlos City Library Tech4ED Center')
                    ->datalist(fn (): array => self::distinctSuggestions('conducted_by'))
                    ->columnSpanFull(),

                TextInput::make('sector')
                    ->label('Sector')
                    ->maxLength(100)
                    ->placeholder('e.g. Others')
                    ->datalist(fn (): array => self::distinctSuggestions('sector')),

                DatePicker::make('graduated_at')
                    ->label('Graduated At')
                    ->afterOrEqual('to_date'),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'COMPLETED'   => 'Completed',
                        'ONGOING'     => 'Ongoing',
                        'DROPPED'     => 'Dropped',
                        'INCOMPLETE'  => 'Incomplete',
                    ])
                    ->default('COMPLETED')
                    ->required(),
            ])
            ->columns(2);
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
