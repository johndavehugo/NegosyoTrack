<?php

namespace App\Filament\Schemas\Msme;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class BusinessForm
{
    public static function make(): array
    {
        return [
            Grid::make(5)
                ->schema([
                    Section::make()
                        ->columnSpan(4)
                        ->schema([
                            Fieldset::make('Business Details')
                                ->schema([
                                    TextInput::make('juri_name')
                                        ->label('Name')
                                        ->disabled()
                                        ->columnSpan(2),

                                    TextInput::make('entity_no')
                                        ->label('Entity No.')
                                        ->disabled(),

                                    TextInput::make('bin_etracs')
                                        ->label('BIN')
                                        ->disabled(),

                                    TextInput::make('organization')
                                        ->label('Organization')
                                        ->disabled(),

                                    DatePicker::make('juri_date_reg')
                                        ->label('Date Registered')
                                        ->disabled(),

                                    Textarea::make('line_of_industry')
                                        ->label('Line of Industry')
                                        ->disabled(),

                                    TextInput::make('nature_of_business')
                                        ->label('Nature of Business')
                                        ->disabled(),
                                ]),

                            Fieldset::make('MSME Classification')
                                ->schema([
                                    Select::make('special_category')
                                        ->label('Special Category')
                                        ->options([
                                            'None' => 'None',
                                            '4ps Benificiary' => '4Ps Beneficiary',
                                            'Solo Parent' => 'Solo Parent',
                                            'Person with Disability (PWD)' => 'Person with Disability (PWD)',
                                            'Young Entrepreneur' => 'Young Entrepreneur',
                                        ])
                                        ->default('None')
                                        ->required(),

                                    TextInput::make('capitalization')
                                        ->label('Capitalization')
                                        ->numeric()
                                        ->prefix('₱')
                                        ->minValue(0)
                                        ->step(0.01)
                                        ->required()
                                        ->helperText('The MSME category is set automatically from this amount.'),
                                ]),

                            Fieldset::make('Address Details')
                                ->schema([
                                    TextInput::make('region')
                                        ->disabled(),
                                    TextInput::make('province')
                                        ->disabled(),
                                    TextInput::make('city')
                                        ->disabled(),
                                    TextInput::make('barangay')
                                        ->disabled(),
                                    TextInput::make('street')
                                        ->disabled(),
                                    TextInput::make('subdivision')
                                        ->disabled(),
                                    TextInput::make('upblb_num')
                                        ->disabled(),
                                    TextInput::make('zip')
                                        ->disabled(),
                                ]),
                        ]),

                    Section::make('Record Information')
                        ->columnSpan(1)
                        ->schema([
                            TextInput::make('status')
                                ->label('Registration Status')
                                ->disabled(),

                            TextInput::make('total_workforce')
                                ->label('Workforce')
                                ->disabled(),
                        ]),
                ]),

        ];
    }
}
