<?php

namespace App\Filament\Resources\Training\Trainees\Schemas;

use App\Models\Training\Training;
use App\Services\Training\ScimsTrainingService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class TraineesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Trainee')
                    ->description('Pick the person from SCIMS. Their name and contact details are saved locally so lists and reports stay fast.')
                    ->icon(Heroicon::User)
                    ->columns(2)
                    ->schema([
                        // Only api_entity_no is the link back to SCIMS; the
                        // identity fields are snapshotted for local use.
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
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                $individual = app(ScimsTrainingService::class)->findIndividual($state);

                                if (filled($individual['mobile_no'] ?? null)) {
                                    $set('contact_no', $individual['mobile_no']);
                                }
                            })
                            ->columnSpanFull(),

                        Placeholder::make('individual_details')
                            ->label('Trainee details')
                            ->content(
                                fn (Get $get): Htmlable => self::individualDetailsHtml($get('api_entity_no'))
                            )
                            ->columnSpanFull(),
                    ]),

                Section::make('Enrollment')
                    ->description('Choose the training this person attended and confirm their contact number.')
                    ->icon(Heroicon::AcademicCap)
                    ->columns(2)
                    ->schema([
                        Select::make('training_id')
                            ->label('Training')
                            ->required()
                            ->searchable()
                            ->options(fn (): array => self::trainingOptions())
                            ->default(function (): ?string {
                                $trainingId = request()->query('training_id');

                                return filled($trainingId) ? (string) $trainingId : null;
                            })
                            ->columnSpanFull(),

                        TextInput::make('contact_no')
                            ->label('Contact No.')
                            ->required()
                            ->tel()
                            ->maxLength(255)
                            ->helperText('Filled from SCIMS when a mobile number is available.'),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected static function trainingOptions(): array
    {
        return Training::query()
            ->orderByDesc('from_date')
            ->limit(500)
            ->get()
            ->mapWithKeys(fn (Training $training): array => [
                $training->id => $training->training_program
                    .($training->from_date ? ' — '.$training->from_date->format('M Y') : ''),
            ])
            ->all();
    }

    protected static function individualDetailsHtml(?string $entityNo): Htmlable
    {
        if (blank($entityNo)) {
            $hint = 'Pick a trainee above to preview their name, gender, birth date and mobile number from SCIMS.';

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
            'Mobile' => filled($individual['mobile_no'] ?? null) ? $individual['mobile_no'] : '—',
        ];

        $blocks = array_map(
            fn (string $label, string $value): string => sprintf(
                '<div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">%s</p><p class="text-sm">%s</p></div>',
                e($label),
                e($value),
            ),
            array_keys($details),
            $details,
        );

        return new HtmlString(
            '<div class="grid grid-cols-1 gap-2 sm:grid-cols-4">'.implode('', $blocks).'</div>',
        );
    }
}
