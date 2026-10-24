<?php

namespace App\Filament\Resources\Training\TrainingPrograms;

use App\Enums\TrainingStatus;
use App\Filament\Resources\Training\TrainingPrograms\Pages\CreateTrainingProgram;
use App\Filament\Resources\Training\TrainingPrograms\Pages\EditTrainingProgram;
use App\Filament\Resources\Training\TrainingPrograms\Pages\ListTrainingPrograms;
use App\Filament\Resources\Training\TrainingPrograms\Pages\ViewTrainingProgram;
use App\Filament\Resources\Training\TrainingPrograms\RelationManagers\TraineesRelationManager;
use App\Filament\Resources\Training\TrainingPrograms\Schemas\TrainingProgramForm;
use App\Filament\Resources\Training\TrainingPrograms\Schemas\TrainingProgramInfolist;
use App\Filament\Resources\Training\TrainingPrograms\Tables\TrainingProgramsTable;
use App\Models\Training\Training;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TrainingProgramResource extends Resource
{
    protected static ?string $model = Training::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AcademicCap;

    protected static ?string $navigationLabel = 'Training Records';

    protected static ?string $pluralModelLabel = 'Training Records';

    protected static ?string $modelLabel = 'Training Record';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'training_program';

    public static function getNavigationGroup(): ?string
    {
        return 'Training & Capacity Building';
    }

    public static function form(Schema $schema): Schema
    {
        return TrainingProgramForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TrainingProgramInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingProgramsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TraineesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainingPrograms::route('/'),
            'create' => CreateTrainingProgram::route('/create'),
            'view' => ViewTrainingProgram::route('/{record}'),
            'edit' => EditTrainingProgram::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $ongoing = Training::query()
            ->where('status', TrainingStatus::Ongoing->value)
            ->count();

        return $ongoing > 0 ? (string) $ongoing : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Ongoing training records';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'training_program',
            'business_name',
            'entity_no',
            'training_type',
            'conducted_by',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Training $record */
        return [
            'Business' => $record->business_display_name,
            'Schedule' => $record->schedule_label,
        ];
    }
}
