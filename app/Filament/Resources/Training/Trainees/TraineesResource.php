<?php

namespace App\Filament\Resources\Training\Trainees;

use App\Filament\Resources\Training\Trainees\Pages\CreateTrainees;
use App\Filament\Resources\Training\Trainees\Pages\EditTrainees;
use App\Filament\Resources\Training\Trainees\Pages\ListTrainees;
use App\Filament\Resources\Training\Trainees\Pages\ViewTrainees;
use App\Filament\Resources\Training\Trainees\Schemas\TraineesForm;
use App\Filament\Resources\Training\Trainees\Schemas\TraineesInfolist;
use App\Filament\Resources\Training\Trainees\Tables\TraineesTable;
use App\Models\Training\Trainee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TraineesResource extends Resource
{
    protected static ?string $model = Trainee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static ?string $navigationLabel = 'Trainees';

    protected static ?string $pluralModelLabel = 'Trainees';

    protected static ?string $modelLabel = 'Trainee';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getNavigationGroup(): ?string
    {
        return 'Training & Capacity Building';
    }

    public static function form(Schema $schema): Schema
    {
        return TraineesForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TraineesInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TraineesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('training');
    }

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        /** @var ?Trainee $record */
        return $record?->display_name ?? static::getModelLabel();
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
            'view' => ViewTrainees::route('/{record}'),
            'edit' => EditTrainees::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $total = Trainee::query()->count();

        return $total > 0 ? (string) $total : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Enrolled trainees';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'full_name',
            'api_entity_no',
            'contact_no',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Trainee $record */
        return [
            'Training' => $record->training?->training_program ?? 'Not enrolled',
            'Entity no.' => $record->api_entity_no,
        ];
    }
}
