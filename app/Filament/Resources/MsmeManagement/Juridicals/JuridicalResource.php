<?php

namespace App\Filament\Resources\MsmeManagement\Juridicals;

use App\Filament\Resources\MsmeManagement\Juridicals\Pages\CreateJuridical;
use App\Filament\Resources\MsmeManagement\Juridicals\Pages\EditJuridical;
use App\Filament\Resources\MsmeManagement\Juridicals\Pages\ListJuridicals;
use App\Filament\Resources\MsmeManagement\Juridicals\Pages\ViewBusiness;
use App\Filament\Resources\MsmeManagement\Juridicals\Schemas\JuridicalForm;
use App\Filament\Resources\MsmeManagement\Juridicals\Tables\JuridicalsTable;
use App\Models\MsmeManagement\Juridical;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JuridicalResource extends Resource
{
    protected static ?string $model = Juridical::class;

    protected static ?string $navigationLabel = 'Business';

    protected static ?string $pluralModelLabel = 'Businesses';

    protected static ?string $modelLabel = 'Business';

    public static function getNavigationGroup(): ?string
    {
        return 'Msme Management';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return JuridicalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JuridicalsTable::configure($table);
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
            'index' => ListJuridicals::route('/'),
            'create' => CreateJuridical::route('/create'),
            'edit' => EditJuridical::route('/{record}/edit'),
            'view' => ViewBusiness::route('/view-business/{record}'),
        ];
    }
}
