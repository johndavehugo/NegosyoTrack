<?php

namespace App\Services\Training;

use App\Models\Training\Trainee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\CSV\Options as CsvOptions;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Downloads the graduate list as a spreadsheet, using the same OpenSpout
 * package as the calamity report exports.
 */
class GraduatesExporter
{
    /**
     * @var list<string>
     */
    protected const HEADERS = [
        'Graduate',
        'SCIMS entity no.',
        'Contact no.',
        'Training program',
        'Training type',
        'Sector',
        'Graduated on',
        'Gender',
    ];

    public function download(Builder $query, string $format = 'csv'): BinaryFileResponse
    {
        $format = strtolower($format);

        if (! in_array($format, ['csv', 'xlsx'], true)) {
            throw new InvalidArgumentException("Unsupported export format [{$format}].");
        }

        /** @var Collection<int, Trainee> $graduates */
        $graduates = $query
            ->with(['training', 'trainingDetails'])
            ->reorder()
            ->get()
            // Names live in SCIMS, so alphabetical ordering happens after the
            // collection's batch identity prefetch rather than in SQL.
            ->sortBy(
                fn (Trainee $graduate): string => $graduate->display_name,
                SORT_NATURAL | SORT_FLAG_CASE,
            )
            ->values();

        $path = tempnam(sys_get_temp_dir(), 'training-graduates-');

        if ($path === false) {
            throw new RuntimeException('Unable to create a temporary graduates file.');
        }

        if ($format === 'csv') {
            $this->writeCsv($path, $graduates);
        } else {
            $this->writeXlsx($path, $graduates);
        }

        return response()
            ->download($path, 'training-graduates-'.now()->format('Y-m-d-His').'.'.$format, [
                'Content-Type' => $format === 'csv'
                    ? 'text/csv; charset=UTF-8'
                    : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    /**
     * @param  Collection<int, Trainee>  $graduates
     */
    protected function writeCsv(string $path, Collection $graduates): void
    {
        $writer = new CsvWriter(new CsvOptions);
        $writer->openToFile($path);

        foreach ($this->rows($graduates) as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();
    }

    /**
     * @param  Collection<int, Trainee>  $graduates
     */
    protected function writeXlsx(string $path, Collection $graduates): void
    {
        $writer = new XlsxWriter;
        $writer->openToFile($path);

        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Graduates');

        foreach ([30, 18, 18, 45, 25, 22, 14, 12] as $index => $width) {
            $sheet->setColumnWidth($width, $index + 1);
        }

        foreach ($this->rows($graduates) as $index => $row) {
            $writer->addRow(Row::fromValues($row, $index === 0 ? $this->headerStyle() : null));
        }

        $writer->close();
    }

    /**
     * @param  Collection<int, Trainee>  $graduates
     * @return iterable<int, array<int, string|null>>
     */
    protected function rows(Collection $graduates): iterable
    {
        yield self::HEADERS;

        foreach ($graduates as $graduate) {
            yield [
                $this->safe($graduate->display_name),
                $this->safe($graduate->api_entity_no),
                $this->safe($graduate->contact_no),
                $this->safe($graduate->training?->training_program),
                $this->safe($graduate->training?->training_type),
                $this->safe($graduate->training?->sector),
                $graduate->trainingDetails?->graduated_at?->format('Y-m-d'),
                $this->safe($graduate->gender),
            ];
        }
    }

    protected function headerStyle(): Style
    {
        return (new Style)
            ->setFontBold()
            ->setBackgroundColor('F3F4F6');
    }

    /**
     * Prevent spreadsheet formula injection for user-entered values.
     */
    protected function safe(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return preg_match('/^[=+@\t\r-]/', $value) === 1
            ? "'".$value
            : $value;
    }
}
