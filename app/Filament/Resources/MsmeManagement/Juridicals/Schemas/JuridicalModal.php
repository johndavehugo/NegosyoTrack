<?php

namespace App\Filament\Resources\MsmeManagement\Juridicals\Schemas;

use App\Models\MsmeManagement\Juridical;
use Filament\Actions\Action;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class JuridicalModal
{
    public static function RenewAction(): Action
    {
        return Action::make('renew')
            ->label('Renew')
            ->icon(Heroicon::CreditCard)
            ->color(Color::Green)
            ->modalHeading('Renew this Business?')
            ->modalIcon(Heroicon::CreditCard)
            ->modalWidth('sm')
            ->modalFooterActionsAlignment(Alignment::End)
            ->requiresConfirmation()
            ->action(function (Juridical $record): void {
                try {
                    $response = Http::acceptJson()->patch(
                        route('msme.renew', ['juridical' => $record->entity_no]),
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
                    Notification::make()->title($message)->success()->send();
                } else {
                    Notification::make()->title($message)->danger()->send();
                }
            });
    }

    public static function StatusAction(): Action
    {
        return Action::make('status')
            ->label('Change Status')
            ->icon(Heroicon::ArrowPathRoundedSquare)
            ->color(Color::Amber)
            ->modalHeading('Change Business Status')
            ->modalIcon(Heroicon::ArrowPathRoundedSquare)
            ->modalSubmitActionLabel('Update Status')
            ->modalWidth('md')
            ->modalFooterActionsAlignment(Alignment::End)
            ->form([Section::make()
                ->schema([
                    Group::make([
                        TextEntry::make('name')
                            ->hiddenLabel()
                            ->state(fn ($record) => strtoupper($record->name))
                            ->weight(FontWeight::Bold),
                        TextEntry::make('business_code')
                            ->hiddenLabel()
                            ->color('gray')
                            ->state(fn ($record) => $record->entity_no)])
                        ->gap(0),
                ]),

                TextEntry::make('status')
                    ->label('Current Status:')
                    ->badge()
                    ->color(fn ($state) => $state === 'ACTIVE' ? 'success' : 'danger')
                    ->state(fn ($record) => strtoupper($record->bus_status))
                    ->inlineLabel(),

                ToggleButtons::make('bus_status')
                    ->label('↓ Change To')
                    ->options([
                        'ACTIVE' => 'Active',
                        'INACTIVE' => 'Inactive',
                    ])
                    ->icons([
                        'ACTIVE' => 'heroicon-m-check-circle',
                        'INACTIVE' => 'heroicon-m-x-circle',
                    ])
                    ->colors([
                        'ACTIVE' => 'success',
                        'INACTIVE' => 'danger',
                    ])
                    ->inline()
                    ->fullWidth()
                    ->extraAttributes(['class' => 'juridical-status-toggle'])
                    ->size('xl')])
            ->action(function (Juridical $record, array $data): void {
                try {
                    $response = Http::acceptJson()->patch(
                        route('msme.status', ['juridical' => $record->entity_no]),
                        ['bus_status' => $data['bus_status']],
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
                    Notification::make()->title($message)->success()->send();
                } else {
                    Notification::make()->title($message)->danger()->send();
                }
            });
    }
}
