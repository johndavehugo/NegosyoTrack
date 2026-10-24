<?php

namespace App\Http\Controllers;

use App\Filament\Resources\CalamityMonitoring\Calamities\CalamityResource;
use App\Models\CalamityMonitoring\Calamity;
use App\Services\CalamityMonitoring\ReportService;
use Illuminate\Contracts\View\View;

class CalamityReportController extends Controller
{
    public function __invoke(Calamity $calamity, ReportService $reports): View
    {
        return view('calamity-monitoring.report', [
            'report' => $reports->build($calamity),
            'backUrl' => CalamityResource::getUrl('manage-incidents', [
                'record' => $calamity,
            ]),
        ]);
    }
}
