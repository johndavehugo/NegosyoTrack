<?php

namespace App\Services\CalamityMonitoring;

use App\Models\CalamityMonitoring\Calamity;
use App\Models\CalamityMonitoring\Incident;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\CSV\Options as CsvOptions;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExporter
{
    public function __construct(protected ReportService $reports) {}

    public function download(Calamity $calamity, Builder $query, string $format): BinaryFileResponse
    {
        $format = strtolower($format);

        if (! in_array($format, ['csv', 'xlsx'], true)) {
            throw new InvalidArgumentException("Unsupported report format [{$format}].");
        }

        $report = $this->reports->fromIncidents($calamity, $query->get());

        $path = tempnam(sys_get_temp_dir(), 'calamity-report-');

        if ($path === false) {
            throw new RuntimeException('Unable to create a temporary report file.');
        }

        if ($format === 'csv') {
            $this->writeCsv($path, $report);
        } else {
            $this->writeXlsx($path, $report);
        }

        return response()
            ->download($path, $this->filename($calamity, $format), [
                'Content-Type' => $format === 'csv'
                    ? 'text/csv; charset=UTF-8'
                    : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    /**
     * @param  array<string, mixed>  $report
     */
    protected function writeCsv(string $path, array $report): void
    {
        $writer = new CsvWriter(new CsvOptions);
        $writer->openToFile($path);

        $writer->addRow($this->detailHeaderRow());

        foreach ($report['incidents'] as $index => $incident) {
            $writer->addRow($this->detailRow($index + 1, $incident));
        }

        $writer->addRow($this->totalRow((float) $report['summary']['total_damage']));
        $writer->close();
    }

    /**
     * @param  array<string, mixed>  $report
     */
    protected function writeXlsx(string $path, array $report): void
    {
        $writer = new XlsxWriter;
        $writer->openToFile($path);

        $this->writeSummarySheet($writer, $report);
        $this->writeBarangaySheet($writer, $report);
        $this->writeDetailsSheet($writer, $report);

        $writer->close();
    }

    /**
     * @param  array<string, mixed>  $report
     */
    protected function writeSummarySheet(XlsxWriter $writer, array $report): void
    {
        /** @var Calamity $calamity */
        $calamity = $report['calamity'];
        $summary = $report['summary'];

        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Summary');
        $sheet->setColumnWidth(32, 1);
        $sheet->setColumnWidth(34, 2);

        $writer->addRow(Row::fromValues(['MSME Calamity Damage Assessment Report'], $this->titleStyle()));
        $writer->addRow(Row::fromValues(['Calamity', $this->safe($calamity->name)]));
        $writer->addRow(Row::fromValues(['Calamity Type', $this->safe($calamity->calamity_type)]));
        $writer->addRow(Row::fromValues(['Declaration Date', $this->formatDate($calamity->declaration_date)]));
        $writer->addRow(Row::fromValues(['Generated At', $report['generated_at']->format('M d, Y h:i A')]));
        $writer->addRow(Row::fromValues([]));

        $writer->addRow(Row::fromValues(['Overall Summary'], $this->sectionStyle()));
        $writer->addRow(Row::fromValues(['Metric', 'Value'], $this->headerStyle()));
        $writer->addRow(Row::fromValues(['Affected MSMEs', $summary['affected']]));
        $writer->addRow(Row::fromValues(['Incident Records', $summary['incidents']]));
        $writer->addRow($this->moneyRow('Total Estimated Damage', (float) $summary['total_damage']));
        $writer->addRow($this->moneyRow('Average Damage per Incident', (float) $summary['average_damage']));
        $writer->addRow(Row::fromValues(['Verified', $summary['verified']]));
        $writer->addRow(Row::fromValues(['Pending Verification', $summary['pending']]));
        $writer->addRow(Row::fromValues(['Aid Released', $summary['aid_released']]));
        $writer->addRow(Row::fromValues(['Partial Damage', $summary['partial']]));
        $writer->addRow(Row::fromValues(['Total Damage', $summary['total']]));
        $writer->addRow(Row::fromValues([]));

        $writer->addRow(Row::fromValues(['Damage by Status'], $this->sectionStyle()));
        $writer->addRow(Row::fromValues(['Status', 'Incidents', 'Estimated Damage'], $this->headerStyle()));

        foreach ($report['by_status'] as $row) {
            $writer->addRow(Row::fromValuesWithStyles(
                [$row['label'], $row['count'], $row['damage']],
                null,
                [2 => $this->currencyStyle()],
            ));
        }

        $writer->addRow(Row::fromValues([]));

        $writer->addRow(Row::fromValues(['Damage by Classification'], $this->sectionStyle()));
        $writer->addRow(Row::fromValues(['Classification', 'Incidents', 'Estimated Damage'], $this->headerStyle()));

        foreach ($report['by_nature'] as $row) {
            $writer->addRow(Row::fromValuesWithStyles(
                [$row['label'], $row['count'], $row['damage']],
                null,
                [2 => $this->currencyStyle()],
            ));
        }
    }

    /**
     * @param  array<string, mixed>  $report
     */
    protected function writeBarangaySheet(XlsxWriter $writer, array $report): void
    {
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName('Barangay Summary');
        $sheet->setColumnWidth(28, 1);
        $sheet->setColumnWidth(16, 2);
        $sheet->setColumnWidth(12, 3);
        $sheet->setColumnWidth(22, 4);
        $sheet->setColumnWidth(10, 5);

        $writer->addRow(Row::fromValues(['Damage by Barangay'], $this->titleStyle()));
        $writer->addRow(Row::fromValues(['Barangay', 'Affected MSMEs', 'Incidents', 'Estimated Damage', 'Share'], $this->headerStyle()));

        foreach ($report['by_barangay'] as $row) {
            $writer->addRow(Row::fromValuesWithStyles(
                [$row['barangay'], $row['affected'], $row['incidents'], $row['damage'], $row['share'].'%'],
                null,
                [3 => $this->currencyStyle()],
            ));
        }

        $writer->addRow(Row::fromValuesWithStyles(
            [
                'TOTAL',
                $report['summary']['affected'],
                $report['summary']['incidents'],
                $report['summary']['total_damage'],
                '100%',
            ],
            $this->boldStyle(),
            [3 => $this->currencyStyle()],
        ));
    }

    /**
     * @param  array<string, mixed>  $report
     */
    protected function writeDetailsSheet(XlsxWriter $writer, array $report): void
    {
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName('Incident Details');
        $sheet->setColumnWidth(6, 1);
        $sheet->setColumnWidth(18, 2);
        $sheet->setColumnWidth(38, 3);
        $sheet->setColumnWidth(18, 4);
        $sheet->setColumnWidth(14, 5);
        $sheet->setColumnWidth(18, 6);
        $sheet->setColumnWidth(22, 7);
        $sheet->setColumnWidth(20, 8);
        $sheet->setColumnWidth(34, 9);

        $writer->addRow(Row::fromValues(['Affected MSMEs'], $this->titleStyle()));
        $writer->addRow($this->detailHeaderRow());

        foreach ($report['incidents'] as $index => $incident) {
            $writer->addRow($this->detailRow($index + 1, $incident));
        }

        $writer->addRow($this->totalRow((float) $report['summary']['total_damage']));
    }

    protected function detailHeaderRow(): Row
    {
        return Row::fromValues([
            'No.',
            'SCIMS Entity No.',
            'MSME Name',
            'Barangay',
            'Date Occurred',
            'Nature of Damage',
            'Estimated Cost of Damage',
            'Status',
            'Remarks',
        ], $this->headerStyle());
    }

    protected function detailRow(int $index, Incident $incident): Row
    {
        return Row::fromValuesWithStyles([
            $index,
            $this->safe($incident->juridical_id),
            $this->safe($incident->juridical_name ?? $incident->juridical_id),
            $this->safe(trim((string) $incident->barangay) ?: 'Unspecified'),
            $this->formatDate($incident->date_occurred),
            Incident::natureLabel($incident->nature_of_damage),
            (float) $incident->estimated_cost_of_damages,
            Incident::statusLabel($incident->status),
            $this->safe($incident->remarks),
        ], null, [6 => $this->currencyStyle()]);
    }

    protected function totalRow(float $total): Row
    {
        return Row::fromValuesWithStyles(
            ['', '', 'TOTAL', '', '', '', $total, '', ''],
            $this->boldStyle(),
            [6 => $this->currencyStyle()],
        );
    }

    protected function moneyRow(string $label, float $value): Row
    {
        return Row::fromValuesWithStyles([$label, $value], null, [1 => $this->currencyStyle()]);
    }

    protected function filename(Calamity $calamity, string $format): string
    {
        $slug = Str::slug((string) $calamity->name) ?: 'calamity-'.$calamity->getKey();
        $suffix = $format === 'csv' ? 'incidents' : 'report';

        return sprintf(
            'calamity-%s-%s-%s-%s.%s',
            $calamity->getKey(),
            $slug,
            $suffix,
            now()->format('Y-m-d'),
            $format,
        );
    }

    protected function formatDate(mixed $value): string
    {
        if (blank($value)) {
            return '—';
        }

        try {
            return Carbon::parse($value)->format('M d, Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    protected function safe(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return preg_match('/^[=+@\t\r-]/', $value) === 1
            ? "'".$value
            : $value;
    }

    protected function titleStyle(): Style
    {
        return (new Style)->setFontBold()->setFontSize(14);
    }

    protected function sectionStyle(): Style
    {
        return (new Style)->setFontBold()->setFontSize(12);
    }

    protected function headerStyle(): Style
    {
        return (new Style)->setFontBold()->setBackgroundColor('F3F4F6');
    }

    protected function boldStyle(): Style
    {
        return (new Style)->setFontBold();
    }

    protected function currencyStyle(): Style
    {
        return (new Style)->setFormat('"₱"#,##0.00');
    }
}
