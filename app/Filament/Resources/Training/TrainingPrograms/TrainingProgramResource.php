<?php

namespace App\Filament\Resources\Training\TrainingPrograms;

use App\Filament\Resources\Training\TrainingPrograms\Pages\CreateTrainingProgram;
use App\Filament\Resources\Training\TrainingPrograms\Pages\EditTrainingProgram;
use App\Filament\Resources\Training\TrainingPrograms\Pages\ListTrainingPrograms;
use App\Filament\Resources\Training\TrainingPrograms\Schemas\TrainingProgramForm;
use App\Filament\Resources\Training\TrainingPrograms\Tables\TrainingProgramsTable;
use App\Models\Training\TrainingInfo;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrainingProgramResource extends Resource
{
    protected static ?string $model = TrainingInfo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AcademicCap;

    protected static ?string $navigationLabel = 'Training Records';

    protected static ?string $pluralModelLabel = 'Training Records';

    protected static ?string $modelLabel = 'Training Record';

    protected static ?string $recordTitleAttribute = 'training_program';

    public static function getNavigationGroup(): ?string
    {
        return 'Training & Capacity Building';
    }

    public static function form(Schema $schema): Schema
    {
        return TrainingProgramForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingProgramsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTrainingPrograms::route('/'),
            'create' => CreateTrainingProgram::route('/create'),
            'edit'   => EditTrainingProgram::route('/{record}/edit'),
        ];
    }
}
