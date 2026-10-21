<?php

namespace App\Filament\Schemas\Msme;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                            ->disabled()
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
                    ])         
        ];
    }
}
