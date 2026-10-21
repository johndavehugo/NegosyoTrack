<?php

namespace App\Filament\Resources\Training\TrainingPrograms\Schemas;

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
                // Link to business via entity_no
                Select::make('entity_no')
                    ->label('Business (Entity No.)')
                    ->relationship('juridical', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->entity_no})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('training_program')
                    ->label('Training Program')
                    ->required()
                    ->maxLength(300)
                    ->placeholder('e.g. BASIC COMPUTER OPERATIONS & CONCEPTS (96 HRS)')
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
                    ->placeholder('e.g. Digital Literacy Course'),

                TextInput::make('conducted_by')
                    ->label('Conducted By')
                    ->required()
                    ->maxLength(300)
                    ->placeholder('e.g. San Carlos City Library Tech4ED Center')
                    ->columnSpanFull(),

                TextInput::make('sector')
                    ->label('Sector')
                    ->maxLength(100)
                    ->placeholder('e.g. Others'),

                DatePicker::make('graduated_at')
                    ->label('Graduated At'),

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
}
