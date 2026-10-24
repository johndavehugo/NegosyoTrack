<?php

namespace App\Filament\Resources\Training\Trainees;

use App\Filament\Resources\Training\Trainees\Pages\CreateTrainees;
use App\Filament\Resources\Training\Trainees\Pages\EditTrainees;
use App\Filament\Resources\Training\Trainees\Pages\ListTrainees;
use App\Filament\Resources\Training\Trainees\Schemas\TraineesForm;
use App\Filament\Resources\Training\Trainees\Tables\TraineesTable;
use App\Models\Training\Trainee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TraineesResource extends Resource
{
    protected static ?string $model = Trainee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static ?string $navigationLabel = 'Trainees';

    protected static ?string $pluralModelLabel = 'Trainees';

    protected static ?string $modelLabel = 'Trainee';

    protected static ?string $recordTitleAttribute = 'api_entity_no';

    public static function getNavigationGroup(): ?string
    {
        return 'Training & Capacity Building';
    }

    public static function form(Schema $schema): Schema
    {
        return TraineesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TraineesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainees::route('/'),
            'create' => CreateTrainees::route('/create'),
            'edit' => EditTrainees::route('/{record}/edit'),
        ];
    }
}
