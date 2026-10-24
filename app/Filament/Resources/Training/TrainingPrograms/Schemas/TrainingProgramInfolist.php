<?php

namespace App\Filament\Resources\Training\TrainingPrograms\Schemas;

use App\Enums\TrainingStatus;
use App\Models\Training\Training;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TrainingProgramInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Training program')
                    ->icon(Heroicon::AcademicCap)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('training_program')
                            ->label('Program')
                            ->weight('semibold')
                            ->columnSpanFull(),

                        TextEntry::make('training_type')
                            ->label('Training type')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (?TrainingStatus $state): string => $state?->getLabel() ?? '—')
                            ->color(fn (?TrainingStatus $state): string|array|null => $state?->getColor())
                            ->icon(fn (?TrainingStatus $state) => $state?->getIcon()),

                        TextEntry::make('sector')
                            ->label('Sector')
                            ->placeholder('Not set'),

                        TextEntry::make('conducted_by')
                            ->label('Conducted by')
                            ->placeholder('—'),
                    ]),

                Section::make('Business')
                    ->icon(Heroicon::BuildingOffice2)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('business_name')
                            ->label('Business name')
                            ->state(fn (Training $record): string => $record->business_display_name)
                            ->weight('semibold'),

                        TextEntry::make('entity_no')
                            ->label('SCIMS entity no.')
                            ->badge()
                            ->color('gray')
                            ->copyable()
                            ->copyMessage('Copied!'),
                    ]),

                Section::make('Schedule & outcome')
                    ->icon(Heroicon::CalendarDays)
                    ->columns(3)
                    ->schema([
                        TextEntry::make('from_date')
                            ->label('Starts')
                            ->date('M d, Y'),

                        TextEntry::make('to_date')
                            ->label('Ends')
                            ->date('M d, Y'),

                        TextEntry::make('duration_in_days')
                            ->label('Duration')
                            ->state(fn (Training $record): ?int => $record->duration_in_days)
                            ->suffix(' days')
                            ->placeholder('—'),

                        TextEntry::make('hours_no')
                            ->label('Training hours')
                            ->suffix(' hrs'),

                        TextEntry::make('graduated_at')
                            ->label('Graduated')
                            ->date('M d, Y')
                            ->placeholder('Not graduated'),

                        TextEntry::make('trainees_count')
                            ->label('Enrolled trainees')
                            ->state(fn (Training $record): int => $record->trainees()->count())
                            ->badge()
                            ->color('info'),
                    ]),

                Section::make('Record')
                    ->icon(Heroicon::ClipboardDocumentList)
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('id')
                            ->label('Record ID')
                            ->copyable(),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('M d, Y g:i A')
                            ->placeholder('—'),

                        TextEntry::make('updated_at')
                            ->label('Last updated')
                            ->dateTime('M d, Y g:i A')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
