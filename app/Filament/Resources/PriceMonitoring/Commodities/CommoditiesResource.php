<?php

namespace App\Filament\Resources\PriceMonitoring\Commodities;

use App\Filament\Resources\PriceMonitoring\Commodities\Pages\ManageCommodities;
use App\Models\PriceMonitoring\Commodity;
use App\Services\PriceMonitoringService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommoditiesResource extends Resource
{
    protected static ?string $model = Commodity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::RectangleStack;

    protected static ?string $recordTitleAttribute = 'commodity';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Price Monitoring';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_name')
                    ->required(),

                Select::make('brand_id')
                    ->label('Brand')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required()->unique(),
                    ]),

                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),

                Select::make('juridical_id')
                    ->label('Establishment')
                    ->searchable()
                    ->nullable()
                    ->placeholder('Select an establishment')
                    ->getSearchResultsUsing(
                        fn (string $search): array => app(PriceMonitoringService::class)->searchEstablishments($search)
                    )
                    ->getOptionLabelUsing(
                        fn (?string $value): ?string => app(PriceMonitoringService::class)->resolveEstablishmentName($value)
                    ),

                FileUpload::make('image_path')
                    ->label('Picture')
                    ->image()
                    ->disk('public')
                    ->directory('commodities')
                    ->visibility('public')
                    ->imageEditor()
                    ->maxSize(5120)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->columnSpanFull(),

                Repeater::make('variants')
                    ->relationship('variants')
                    ->schema([
                        TextInput::make('unit_of_measure')
                            ->label('Unit / Size')
                            ->required(),
                        TextInput::make('srp')
                            ->label('SRP')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        TextInput::make('prevailing_price')
                            ->label('Prevailing Price')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(3)
                    ->addActionLabel('Add another unit/size')
                    ->defaultItems(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('commodity')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Picture')
                    ->disk('public')
                    ->square()
                    ->size(48),
                TextColumn::make('product_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->sortable()
                    ->label('Category'),
                TextColumn::make('juridical_id')
                    ->label('Establishment')
                    ->formatStateUsing(function (?string $state): string {
                        if (blank($state)) {
                            return '—';
                        }

                        return app(PriceMonitoringService::class)->resolveEstablishmentName($state) ?? $state;
                    })
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variants'),
                TextColumn::make('over_srp_variants_count')
                    ->counts(['variants as over_srp_variants_count' => fn ($query) => $query->overSrp()])
                    ->label('Flagged Variants')
                    ->badge()
                    ->color(fn ($state): array => $state > 0 ? Color::Red : Color::Gray),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->color(Color::Yellow),
                Action::make('uploadPicture')
                    ->label('Add Picture')
                    ->icon(Heroicon::Photo)
                    ->color(Color::Blue)
                    ->modalHeading(fn (Commodity $record) => 'Add Picture — '.$record->product_name)
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Picture')
                            ->image()
                            ->disk('public')
                            ->directory('commodities')
                            ->visibility('public')
                            ->imageEditor()
                            ->required()
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp']),
                    ])
                    ->action(fn (Commodity $record, array $data) => $record->update([
                        'image_path' => $data['image_path'],
                    ]))
                    ->modalSubmitActionLabel('Save Picture'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCommodities::route('/'),
        ];
    }
}
