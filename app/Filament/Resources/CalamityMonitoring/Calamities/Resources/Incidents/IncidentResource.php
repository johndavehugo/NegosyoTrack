<?php

namespace App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents;

use App\Filament\Resources\CalamityMonitoring\Calamities\CalamityResource;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Pages\CreateIncident;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Pages\EditIncident;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Schemas\IncidentForm;
use App\Filament\Resources\CalamityMonitoring\Calamities\Resources\Incidents\Tables\IncidentsTable;
use App\Models\CalamityMonitoring\Incident;
use BackedEnum;
use Filament\Resources\ParentResourceRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IncidentResource extends Resource
{
    protected static ?string $model = Incident::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getParentResourceRegistration(): ?ParentResourceRegistration
{
        return CalamityResource::asParent()
            ->relationship('incident')
            ->inverseRelationship('calamity')
            ->page('manage-incidents'); 
    }

    protected static ?string $recordTitleAttribute = 'incident';

    public static function form(Schema $schema): Schema
    {
        return IncidentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IncidentsTable::configure($table);
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
            'create' => CreateIncident::route('/create'),
            'edit' => EditIncident::route('/{record}/edit'),
        ];
    }
}
