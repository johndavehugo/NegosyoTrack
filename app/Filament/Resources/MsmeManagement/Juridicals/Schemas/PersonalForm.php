<?php

namespace App\Filament\Resources\MsmeManagement\Juridicals\Schemas;

use App\Models\MsmeManagement\Juridical;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class PersonalForm
{
    public static function make(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('entity_no')
                            ->label('Entity No.')
                            ->readOnly(),

                        TextInput::make('full_name')
                            ->label('Full Name')
                            ->readOnly(),

                        Select::make('gender')
                            ->label('Gender')
                            ->options([
                                'Male' => 'Male',
                                'Female' => 'Female',
                            ])
                            ->required(),

                        DatePicker::make('birth_date')
                            ->label('Date of Birth')
                            ->readOnly(),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->schema([
                        TextInput::make('contact_no')
                            ->label('Contact No.'),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email(),
                    ])
                    ->columns(2),

                Section::make('Classification')
                    ->schema([
                        Select::make('special_category')
                            ->label('Special Category')
                            ->options([
                                'None' => 'None',
                                '4ps Benificiary' => '4ps Benificiary',
                                'Solo Parent' => 'Solo Parent',
                                'Person with Disability (PWD)' => 'Person with Disability (PWD)',
                                'Young Entrepreneur' => 'Young Entrepreneur',

                            ]),
                    ]),
                Actions::make([
                    Action::make('save')
                        ->label('Save')
                        ->icon(Heroicon::Check)
                        ->color('success')
                        ->action(function (Juridical $record, $livewire): void {
                            $data = $livewire->personalData;
                            try {
                                $response = Http::acceptJson()->put(
                                    route('msme.employer.update', ['employer' => $record->employer->entity_no]),
                                    [
                                        'gender' => $data['gender'],
                                        'contact_no' => $data['contact_no'] ?? null,
                                        'email' => $data['email'] ?? null,
                                        'special_category' => $data['special_category'] ?? 'None',
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
                                Notification::make()->title($message)->success()->send();
                            } else {
                                Notification::make()->title($message)->danger()->send();
                            }
                        }),
                ])
                    ->alignEnd(),

            ]);
    }
}
