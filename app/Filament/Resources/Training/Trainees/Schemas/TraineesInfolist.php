<?php

namespace App\Filament\Resources\Training\Trainees\Schemas;

use App\Enums\TrainingStatus;
use App\Filament\Resources\Training\TrainingPrograms\TrainingProgramResource;
use App\Models\Training\Trainee;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TraineesInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('SCIMS identity')
                    ->description('The person snapshot saved with this enrollment.')
                    ->icon(Heroicon::Identification)
                    ->columns(3)
                    ->schema([
                        TextEntry::make('full_name')
                            ->label('Name')
                            ->state(fn (Trainee $record): string => $record->display_name)
                            ->weight('semibold')
                            ->columnSpanFull(),

                        TextEntry::make('api_entity_no')
                            ->label('SCIMS entity no.')
                            ->badge()
                            ->color('gray')
                            ->copyable()
                            ->copyMessage('Copied!'),

                        TextEntry::make('gender')
                            ->label('Gender')
                            ->placeholder('—'),

                        TextEntry::make('age')
                            ->label('Age')
                            ->state(fn (Trainee $record): ?int => $record->age)
                            ->suffix(' years old')
                            ->placeholder('—'),

                        TextEntry::make('birth_date')
                            ->label('Birth date')
                            ->date('M d, Y')
                            ->placeholder('—'),

                        TextEntry::make('mobile_no')
                            ->label('SCIMS mobile')
                            ->icon(Heroicon::Phone)
                            ->copyable()
                            ->placeholder('—'),

                        TextEntry::make('contact_no')
                            ->label('Contact no.')
                            ->icon(Heroicon::Phone)
                            ->copyable()
                            ->placeholder('—'),
                    ]),

                Section::make('Enrollment')
                    ->description('Open the training record to see the full program details and roster.')
                    ->icon(Heroicon::AcademicCap)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('training.training_program')
                            ->label('Training')
                            ->weight('semibold')
                            ->placeholder('Not enrolled')
                            ->url(fn (Trainee $record): ?string => $record->training
                                ? TrainingProgramResource::getUrl('view', ['record' => $record->training])
                                : null)
                            ->columnSpanFull(),

                        TextEntry::make('training.business_name')
                            ->label('Business')
                            ->state(fn (Trainee $record): string => $record->training?->business_display_name ?? '—'),

                        TextEntry::make('training.schedule_label')
                            ->label('Schedule')
                            ->state(fn (Trainee $record): string => $record->training?->schedule_label ?? '—'),

                        TextEntry::make('training.status')
                            ->label('Training status')
                            ->badge()
                            ->formatStateUsing(fn (?TrainingStatus $state): string => $state?->getLabel() ?? '—')
                            ->color(fn (?TrainingStatus $state): string|array|null => $state?->getColor())
                            ->icon(fn (?TrainingStatus $state) => $state?->getIcon()),

                        TextEntry::make('training.conducted_by')
                            ->label('Conducted by')
                            ->placeholder('—'),
                    ]),

                Section::make('Graduation')
                    ->description('A personal completion record puts this trainee on the Graduates page.')
                    ->icon(Heroicon::CheckBadge)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('graduation_status')
                            ->label('Graduation status')
                            ->state(fn (Trainee $record): string => $record->trainingDetails?->status?->getLabel() ?? 'Not graduated')
                            ->badge()
                            ->color(fn (Trainee $record): string|array|null => $record->trainingDetails?->status?->getColor() ?? 'gray'),

                        TextEntry::make('trainingDetails.graduated_at')
                            ->label('Graduated on')
                            ->date('M d, Y')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
