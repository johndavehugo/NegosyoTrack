<?php

namespace App\Filament\Resources\Training\Trainees\Schemas;

use App\Models\Training\Training;
use App\Services\Training\ScimsTrainingService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class TraineesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // The person is picked from the SCIMS individuals API.
                // Only api_entity_no is stored locally — it is the key used
                // to link our records back to the API person record.
                Select::make('api_entity_no')
                    ->label('Trainee (from SCIMS)')
                    ->required()
                    ->searchable()
                    ->live()
                    ->getSearchResultsUsing(
                        fn (string $search): array => app(ScimsTrainingService::class)
                            ->individualOptions($search)
                    )
                    ->getOptionLabelUsing(
                        fn (string $value): string => app(ScimsTrainingService::class)
                            ->individualLabel($value)
                    )
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),

                Placeholder::make('individual_details')
                    ->label('Trainee Details')
                    ->content(
                        fn (Get $get): Htmlable => self::individualDetailsHtml($get('api_entity_no'))
                    ),

                Select::make('training_id')
                    ->label('Training')
                    ->required()
                    ->searchable()
                    ->options(fn (): array => Training::query()
                        ->orderByDesc('created_at')
                        ->limit(500)
                        ->pluck('training_program', 'id')
                        ->toArray()),

                TextInput::make('contact_no')
                    ->label('Contact No.')
                    ->required()
                    ->tel()
                    ->maxLength(255),
            ])
            ->columns(2);
    }

    protected static function individualDetailsHtml(?string $entityNo): Htmlable
    {
        if (blank($entityNo)) {
            $hint = 'Pick a trainee above to preview their name, gender and birth date from SCIMS.';

            return new HtmlString('<p class="text-gray-500 dark:text-gray-400">'.e($hint).'</p>');
        }

        $individual = app(ScimsTrainingService::class)->findIndividual($entityNo);

        if (blank($individual)) {
            $notice = 'SCIMS details unavailable for ('.$entityNo.') — the entity_no will still be saved.';

            return new HtmlString('<p class="text-gray-500 dark:text-gray-400">'.e($notice).'</p>');
        }

        $details = [
            'Name' => ScimsTrainingService::formatName($individual),
            'Gender' => filled($individual['gender'] ?? null) ? $individual['gender'] : '—',
            'Birth date' => filled($individual['birth_date'] ?? null) ? $individual['birth_date'] : '—',
        ];

        $blocks = array_map(
            fn (string $label, string $value): string => sprintf(
                '<div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">%s</p><p class="text-sm">%s</p></div>',
                e($label),
                e($value)
            ),
            array_keys($details),
            $details
        );

        return new HtmlString(
            '<div class="grid grid-cols-1 gap-2 sm:grid-cols-3">'.implode('', $blocks).'</div>'
        );
    }
}
