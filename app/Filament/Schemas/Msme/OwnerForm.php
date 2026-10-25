<?php

namespace App\Filament\Schemas\Msme;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class OwnerForm
{
    public static function make(): array
    {
        return [
            Section::make('Basic Information')
                ->schema([
                    TextInput::make('owner_fullname')
                        ->label('Full Name')
                        ->disabled()
                        ->columnSpan(2),
                ])
                ->columns(2),

            Section::make('Contact Information')
                ->schema([
                    TextInput::make('owner_mobile')
                        ->label('Mobile No.')
                        ->disabled(),

                    TextInput::make('owner_telno')
                        ->label('Telephone No.')
                        ->disabled(),
                    TextInput::make('owner_email')
                        ->label('E-mail')
                        ->disabled(),
                ])
                ->columns(2),
        ];
    }
}
