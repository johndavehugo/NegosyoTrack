<?php

namespace App\Filament\Resources\Training\TrainingPrograms\RelationManagers;

use App\Filament\Resources\Training\Trainees\TraineesResource;
use App\Models\Training\Trainee;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TraineesRelationManager extends RelationManager
{
    protected static string $relationship = 'trainees';

    protected static ?string $title = 'Trainees';

    protected static string|BackedEnum|null $icon = Heroicon::UserGroup;

    protected static bool $isBadgeDeferred = true;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->trainees()->count();
    }

    public static function getBadgeColor(Model $ownerRecord, string $pageClass): ?string
    {
        return 'info';
    }

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Trainee')
                    ->state(fn (Trainee $record): string => $record->display_name)
                    ->description(fn (Trainee $record): string => $record->api_entity_no)
                    ->searchable(['full_name', 'api_entity_no', 'contact_no'])
                    ->sortable()
                    ->weight('semibold')
                    ->limit(45),

                TextColumn::make('gender')
                    ->label('Gender')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('age')
                    ->label('Age')
                    ->state(fn (Trainee $record): ?int => $record->age)
                    ->suffix(' yrs')
                    ->placeholder('—')
                    ->alignCenter(),

                TextColumn::make('contact_no')
                    ->label('Contact')
                    ->icon(Heroicon::Phone)
                    ->copyable()
                    ->placeholder('—'),
            ])
            ->defaultSort('full_name')
            ->recordUrl(fn (Trainee $record): string => TraineesResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('addTrainee')
                    ->label('Add trainee')
                    ->icon(Heroicon::UserPlus)
                    ->url(fn (): string => TraineesResource::getUrl('create', [
                        'training_id' => $this->getOwnerRecord()->getKey(),
                    ])),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('View trainee')
                        ->icon(Heroicon::Eye)
                        ->url(fn (Trainee $record): string => TraineesResource::getUrl('view', ['record' => $record])),

                    Action::make('edit')
                        ->label('Edit trainee')
                        ->icon(Heroicon::PencilSquare)
                        ->url(fn (Trainee $record): string => TraineesResource::getUrl('edit', ['record' => $record])),

                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::UserGroup)
            ->emptyStateHeading('No trainees in this record')
            ->emptyStateDescription('Add the people who attended this training.')
            ->emptyStateActions([
                Action::make('addTrainee')
                    ->label('Add trainee')
                    ->icon(Heroicon::UserPlus)
                    ->url(fn (): string => TraineesResource::getUrl('create', [
                        'training_id' => $this->getOwnerRecord()->getKey(),
                    ])),
            ]);
    }
}
