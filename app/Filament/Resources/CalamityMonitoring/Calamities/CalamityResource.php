<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities;

use App\Filament\Resources\CalamityMonitoring\Calamities\Pages\CreateCalamity;
use App\Filament\Resources\CalamityMonitoring\Calamities\Pages\EditCalamity;
use App\Filament\Resources\CalamityMonitoring\Calamities\Pages\ListCalamities;
use App\Filament\Resources\CalamityMonitoring\Calamities\Schemas\CalamityForm;
use App\Filament\Resources\CalamityMonitoring\Calamities\Tables\CalamitiesTable;
use BackedEnum;
use Calamity;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CalamityResource extends Resource
{
    protected static ?string $model = Calamity::class;

    public static function getNavigationGroup(): ?string
    {
        return 'Calamity Monitoring';
    }

    protected static ?string $slug = 'calamities';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cloud;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CalamityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CalamitiesTable::configure($table);
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
            'index' => ListCalamities::route('/'),
            'create' => CreateCalamity::route('/create'),
            'edit' => EditCalamity::route('/{record}/edit'),
        ];
    }
}
