<?php

namespace App\Filament\Resources\MsmeManagement\Juridicals\Tables;

use App\Filament\Resources\MsmeManagement\Juridicals\JuridicalResource;
use App\Filament\Resources\MsmeManagement\Juridicals\Pages\ViewBusiness;
use App\Filament\Resources\MsmeManagement\Juridicals\Schemas\JuridicalModal;
use App\Models\MsmeManagement\Juridical;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JuridicalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(
                fn (Juridical $record): string => JuridicalResource::getUrl('view', ['record' => $record])
            )
            ->columns([
                TextColumn::make('entity_no')
                    ->label('Entity No.')
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Business Name')
                    ->sortable()
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('category')
                    ->label('Category')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->icon(fn (string $state): Heroicon => match ($state) {
                        'MICRO' => Heroicon::Minus,
                        'SMALL' => Heroicon::Bars2,
                        'MEDIUM' => Heroicon::Bars3,
                        'LARGE' => Heroicon::Bars4
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'MICRO' => 'blue-badge-1',
                        'SMALL' => 'blue-badge-2',
                        'MEDIUM' => 'blue-badge-3',
                        'LARGE' => 'blue-badge-4'
                    }),

                TextColumn::make('capitalization')
                    ->label('Capitalization')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('bus_status')
                    ->label('Status')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->icon(fn (string $state): Heroicon => match ($state) {
                        'ACTIVE' => Heroicon::Play,
                        'INACTIVE' => Heroicon::XMark
                    })
                    ->color(fn (string $state): array => match ($state) {
                        'ACTIVE' => Color::Green,
                        'INACTIVE' => Color::Red
                    }),

                TextColumn::make('registration_type')
                    ->label('Registration Type')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('line_of_industry')
                    ->label('Line of Industry')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                Filter::make('check_bus_status')
                    ->query(fn (Builder $query) => $query->where('bus_status', 'ACTIVE'))
                    ->label('Only show active Businesses'),
                SelectFilter::make('category')
                    ->label('Filter by Category')
                    ->options([
                        'MICRO' => 'Micro',
                        'SMALL' => 'Small',
                        'MEDIUM' => 'Medium',
                        'LARGE' => 'Large',
                    ]),

                SelectFilter::make('registration_type')
                    ->label('Filter by Registration Type')
                    ->options([
                        'NEW' => 'New',
                        'RENEWAL' => 'Renewal',
                    ]),
            ])

            ->actions([
                ActionGroup::make([
                    Action::make('view')
                        ->color(Color::Blue)
                        ->icon(Heroicon::Eye)
                        ->label('View')
                        ->url(fn ($record) => ViewBusiness::getUrl([
                            'record' => $record,
                        ])),

                    JuridicalModal::RenewAction(),

                    JuridicalModal::StatusAction(),
                ]),
            ]);
    }
}
