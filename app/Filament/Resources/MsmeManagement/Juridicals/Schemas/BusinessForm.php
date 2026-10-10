<?php

namespace App\Filament\Resources\MsmeManagement\Juridicals\Schemas;

use App\Models\MsmeManagement\Juridical;
use App\Enums\Industries;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class BusinessForm
{
    public static function make(
        Schema $schema,
        callable $record,
    ): Schema {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make()
                            ->columnSpan(2)
                            ->schema([
                                Fieldset::make('Business Details')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Business Name')
                                            ->readOnly(),

                                        TextInput::make('entity_no')
                                            ->label('Entity No.')
                                            ->readOnly(),

                                        TextInput::make('category')
                                            ->label('Category')
                                            ->readOnly(),

                                        DatePicker::make('date_reg')
                                            ->label('Date Registered')
                                            ->readOnly(),
                                    ]),

                                Fieldset::make('Contact and Financial Details')
                                    ->schema([
                                        TextInput::make('contact_no')
                                            ->label('Contact No.'),

                                        TextInput::make('contact_email')
                                            ->label('E-mail')
                                            ->email(),

                                        Select::make('line_of_industry')
                                            ->label('Line of Industry')
                                            ->options(Industries::FillSelect()),

                                        TextInput::make('capitalization')
                                            ->label('Capitalization')
                                            ->prefix('₱')
                                            ->extraInputAttributes([
                                                'oninput' => "this.value = this.value.replace(/[^0-9.,]/g, '')",
                                                'onblur' => "let val = parseFloat(this.value.replace(/,/g, '')); if (!isNaN(val)) { this.value = val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }",
                                            ])
                                            ->formatStateUsing(fn ($state) => filled($state)
                                                ? number_format((float) $state, 2, '.', ',')
                                                : null),  
                                    ])                             
                            ]),

                        Section::make('Record Information')
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('registration_type')
                                    ->label('Registration Type')
                                    ->state(fn () => $record()->registration_type),

                                TextEntry::make('bus_status')
                                    ->label('Status')
                                    ->state(fn () => $record()->bus_status),
                            ]),

                        Actions::make([
                            Action::make('save')
                                ->icon(Heroicon::Check)
                                ->color('success')
                                ->action(function (Juridical $record, $livewire): void {
                                    $data = $livewire->businessData;
                                    try {
                                        $response = Http::acceptJson()->put(
                                            route('msme.juridical.update', ['juridical' => $record->entity_no]),
                                            [
                                                'contact_no' => $data['contact_no'] ?? null,
                                                'contact_email' => $data['contact_email'] ?? null,
                                                'line_of_industry' => $data['line_of_industry'],
                                                'capitalization' => str_replace(',', '', $data['capitalization']) ?? 0.00,
                                            ],
                                        );
                                    } catch (ConnectionException) {
                                        Notification::make()
                                            ->title('Could not reach the server')
                                            ->danger()
                                            ->send();

                                        return;
                                    }
                                    $message = $response->json('message');

                                    if ($response->successful()) {
                                        Notification::make()
                                            ->title($message)
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title($message)
                                            ->danger()
                                            ->send();
                                    }
                                }),
                        ])
                            ->alignEnd()
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
