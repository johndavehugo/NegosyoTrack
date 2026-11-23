<?php

namespace App\Filament\Resources\PriceMonitoring\PriceLists\Tables;

use App\Models\PriceMonitoring\Agency;
use App\Models\PriceMonitoring\Category;
use App\Models\PriceMonitoring\CommodityVariant;
use App\Services\PriceMonitoringService;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PriceListsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('commodity.product_name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('commodity.brand.name')
                    ->label('Brand')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('commodity.category.name')
                    ->sortable()
                    ->label('Category')
                    ->limit(20)
                    ->tooltip(fn (CommodityVariant $record) => $record->commodity->category->name ?? null),
                TextColumn::make('unit_of_measure')
                    ->label('Unit / Size')
                    ->sortable(),
                TextColumn::make('commodity.category.agency.name')
                    ->sortable()
                    ->label('Agency'),
                TextColumn::make('commodity.juridical_id')
                    ->label('Establishment')
                    ->formatStateUsing(function (?string $state): string {
                        if (blank($state)) {
                            return '—';
                        }

                        return app(PriceMonitoringService::class)->resolveEstablishmentName($state) ?? $state;
                    })
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('srp')
                    ->label('SRP')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('prevailing_price')
                    ->label('Prevailing Price')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->placeholder('—')
                    ->color(fn (CommodityVariant $record) => $record->isOverSrp() ? Color::Red : null)
                    ->weight(fn (CommodityVariant $record) => $record->isOverSrp() ? 'bold' : null)
                    ->icon(fn (CommodityVariant $record) => $record->isOverSrp() ? Heroicon::ExclamationTriangle : null)
                    ->tooltip(function (CommodityVariant $record): ?string {
                        $increase = $record->priceIncreasePercent();

                        if ($increase === null) {
                            return null;
                        }

                        return sprintf(
                            '%+.2f%% vs SRP',
                            $increase,
                        ).($record->isOverSrp() ? ' — flagged above SRP threshold' : '');
                    }),
                TextColumn::make('srp_flag')
                    ->label('SRP Flag')
                    ->badge()
                    ->state(fn (CommodityVariant $record): string => static::srpFlagState($record))
                    ->color(fn (CommodityVariant $record): array => static::srpFlagColor($record))
                    ->icon(fn (CommodityVariant $record): Heroicon => static::srpFlagIcon($record)),
            ])
            ->filters([
                Filter::make('over_srp')
                    ->label('Above SRP (>10%)')
                    ->toggle()
                    ->query(fn ($query, array $data) => $query->when(
                        $data['isActive'] ?? false,
                        fn ($q) => $q->overSrp(),
                    )),
                SelectFilter::make('agency-filter')
                    ->label('Agency')
                    ->options(fn () => Agency::pluck('name', 'id'))
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['values'] ?? null,
                            fn ($q, $values) => $q->whereHas(
                                'commodity.category',
                                fn ($q2) => $q2->whereIn('agency_id', $values)
                            )
                        );
                    })
                    ->multiple(),
                SelectFilter::make('category-filter')
                    ->label('Category')
                    ->options(fn () => Category::pluck('name', 'id'))
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['values'] ?? null,
                            fn ($q, $values) => $q->whereHas(
                                'commodity',
                                fn ($q2) => $q2->whereIn('category_id', $values)
                            )
                        );
                    })
                    ->multiple(),
            ])
            ->recordUrl(null)
            ->recordAction('edit_price_list')
            ->recordClasses(fn (CommodityVariant $record): ?string => $record->isOverSrp()
                ? 'bg-danger-50 dark:bg-danger-950/40'
                : null)
            ->actions([
                EditAction::make('edit_price_list')
                    ->color(Color::Yellow)
                    ->modal()
                    ->schema([
                        TextInput::make('srp')
                            ->label('SRP')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->live(onBlur: true),
                        TextInput::make('prevailing_price')
                            ->label('Prevailing Price')
                            ->numeric()
                            ->minValue(0)
                            ->live(onBlur: true),
                        TextEntry::make('srp_variance')
                            ->hiddenLabel()
                            ->state(fn (Get $get): string => static::srpVarianceMessage($get))
                            ->color(fn (Get $get): array => static::srpVarianceColor($get))
                            ->icon(fn (Get $get): ?Heroicon => static::srpVarianceIcon($get)),
                    ]),
            ]);
    }

    protected static function srpFlagState(CommodityVariant $record): string
    {
        if ($record->srp === null || (float) $record->srp <= 0.0) {
            return 'No SRP';
        }

        if ($record->prevailing_price === null) {
            return 'No Price';
        }

        if ($record->isOverSrp()) {
            return 'Flagged';
        }

        return $record->priceIncreasePercent() > 0 ? 'Above SRP' : 'Within SRP';
    }

    protected static function srpFlagColor(CommodityVariant $record): array
    {
        return match (static::srpFlagState($record)) {
            'Flagged' => Color::Red,
            'Above SRP' => Color::Yellow,
            'Within SRP' => Color::Green,
            default => Color::Gray,
        };
    }

    protected static function srpFlagIcon(CommodityVariant $record): Heroicon
    {
        return match (static::srpFlagState($record)) {
            'Flagged' => Heroicon::ExclamationTriangle,
            'Above SRP' => Heroicon::ArrowTrendingUp,
            'Within SRP' => Heroicon::CheckBadge,
            default => Heroicon::MinusCircle,
        };
    }

    protected static function srpVarianceMessage(Get $get): string
    {
        $srp = (float) ($get('srp') ?? 0);
        $prevailingPrice = $get('prevailing_price');

        if ($srp <= 0.0) {
            return 'Enter an SRP to compute the variance.';
        }

        if ($prevailingPrice === null || $prevailingPrice === '') {
            return 'Enter a prevailing price to compute the variance.';
        }

        $increase = (((float) $prevailingPrice - $srp) / $srp) * 100;
        $formatted = sprintf('%+.2f%% vs SRP', $increase);

        return $increase > CommodityVariant::SRP_FLAG_THRESHOLD_PERCENT
            ? "{$formatted} — flagged as above SRP."
            : "{$formatted} — within the allowed range.";
    }

    protected static function srpVarianceColor(Get $get): array
    {
        $srp = (float) ($get('srp') ?? 0);
        $prevailingPrice = $get('prevailing_price');

        if ($srp <= 0.0 || $prevailingPrice === null || $prevailingPrice === '') {
            return Color::Gray;
        }

        $increase = (((float) $prevailingPrice - $srp) / $srp) * 100;

        return $increase > CommodityVariant::SRP_FLAG_THRESHOLD_PERCENT
            ? Color::Red
            : Color::Green;
    }

    protected static function srpVarianceIcon(Get $get): ?Heroicon
    {
        $srp = (float) ($get('srp') ?? 0);
        $prevailingPrice = $get('prevailing_price');

        if ($srp <= 0.0 || $prevailingPrice === null || $prevailingPrice === '') {
            return null;
        }

        $increase = (((float) $prevailingPrice - $srp) / $srp) * 100;

        return $increase > CommodityVariant::SRP_FLAG_THRESHOLD_PERCENT
            ? Heroicon::ExclamationTriangle
            : Heroicon::CheckBadge;
    }
}
