<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $report['calamity']->name }} - Damage Assessment Report</title>
    <style>
        :root {
            --ink: #111827;
            --muted: #6b7280;
            --line: #d1d5db;
            --soft-line: #e5e7eb;
            --brand: #b91c1c;
            --soft: #f9fafb;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #f3f4f6;
            color: var(--ink);
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 11px;
            line-height: 1.45;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            max-width: 210mm;
            margin: 18px auto 0;
            padding: 0 4px;
        }

        .toolbar a,
        .toolbar button {
            border: 1px solid var(--line);
            border-radius: 6px;
            background: #fff;
            color: var(--ink);
            cursor: pointer;
            font: inherit;
            font-weight: 600;
            padding: 8px 14px;
            text-decoration: none;
        }

        .toolbar button {
            background: var(--brand);
            border-color: var(--brand);
            color: #fff;
        }

        .report {
            max-width: 210mm;
            margin: 18px auto 32px;
            padding: 18mm 16mm;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
        }

        .report-header {
            padding-bottom: 14px;
            border-bottom: 2px solid var(--brand);
            text-align: center;
        }

        .republic { font-size: 12px; letter-spacing: .08em; text-transform: uppercase; }
        .city { font-size: 16px; font-weight: 700; margin-top: 2px; }
        .office { color: var(--muted); font-size: 12px; margin-top: 1px; }

        h1 {
            font-size: 18px;
            margin: 14px 0 4px;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .subtitle { color: var(--muted); margin: 0; }

        .meta {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 16px;
        }

        .meta div {
            border: 1px solid var(--soft-line);
            border-radius: 6px;
            padding: 8px 10px;
        }

        .meta span {
            color: var(--muted);
            display: block;
            font-size: 9px;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .meta strong { display: block; margin-top: 2px; }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 14px;
        }

        .stat-card {
            background: var(--soft);
            border: 1px solid var(--soft-line);
            border-radius: 6px;
            padding: 10px 12px;
        }

        .stat-card span {
            color: var(--muted);
            display: block;
            font-size: 9px;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .stat-card strong {
            display: block;
            font-size: 15px;
            margin-top: 3px;
        }

        h2 {
            border-bottom: 1px solid var(--line);
            font-size: 13px;
            margin: 22px 0 8px;
            padding-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid var(--soft-line);
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: var(--soft);
            font-size: 9px;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        td.num,
        th.num { text-align: right; }

        tfoot td { font-weight: 700; }

        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .muted { color: var(--muted); }
        .empty { color: var(--muted); font-style: italic; padding: 10px 0; }

        .details { font-size: 9.5px; }
        .details td { word-break: break-word; }

        .photo-group {
            border: 1px solid var(--soft-line);
            border-radius: 6px;
            margin-top: 10px;
            padding: 8px 10px;
            page-break-inside: avoid;
        }

        .photo-caption {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
        }

        .photo-caption span { color: var(--muted); }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .photo-item {
            background: var(--soft);
            border: 1px solid var(--soft-line);
            border-radius: 4px;
            margin: 0;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .photo-item img {
            display: block;
            height: 42mm;
            object-fit: contain;
            width: 100%;
        }

        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
            margin-top: 36px;
        }

        .signature { text-align: center; }

        .signature span {
            color: var(--muted);
            display: block;
            font-size: 9px;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .signature .line {
            border-bottom: 1px solid var(--ink);
            height: 34px;
            margin: 0 auto 4px;
            width: 80%;
        }

        .signature small { color: var(--muted); font-size: 9px; }

        .report-footer {
            border-top: 1px solid var(--soft-line);
            color: var(--muted);
            font-size: 8.5px;
            margin-top: 24px;
            padding-top: 8px;
            text-align: center;
        }

        @media print {
            @page { size: A4; margin: 14mm 12mm 16mm; }

            body { background: #fff; }

            .report {
                box-shadow: none;
                margin: 0;
                max-width: none;
                padding: 0;
            }

            .no-print { display: none !important; }

            thead { display: table-header-group; }

            tr,
            .stat-card,
            .signature { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <a href="{{ $backUrl }}">Back to incidents</a>
        <button type="button" onclick="window.print()">Print / Save as PDF</button>
    </div>

    <main class="report">
        @php
            $calamity = $report['calamity'];
            $summary = $report['summary'];
        @endphp

        <header class="report-header">
            <div class="republic">Republic of the Philippines</div>
            <div class="city">City of San Carlos</div>
            <div class="office">Negosyo Center</div>
            <h1>MSME Calamity Damage Assessment Report</h1>
            <p class="subtitle">Generated {{ $report['generated_at']->format('F j, Y g:i A') }}</p>
        </header>

        <section class="meta">
            <div>
                <span>Calamity</span>
                <strong>{{ $calamity->name }}</strong>
            </div>
            <div>
                <span>Type</span>
                <strong>{{ str($calamity->calamity_type)->replace('_', ' ')->title() }}</strong>
            </div>
            <div>
                <span>Declaration Date</span>
                <strong>{{ $calamity->declaration_date ? \Illuminate\Support\Carbon::parse($calamity->declaration_date)->format('F j, Y') : '—' }}</strong>
            </div>
            <div>
                <span>Report Date</span>
                <strong>{{ $report['generated_at']->format('F j, Y') }}</strong>
            </div>
        </section>

        <section class="stats">
            <div class="stat-card">
                <span>Affected MSMEs</span>
                <strong>{{ number_format($summary['affected']) }}</strong>
            </div>
            <div class="stat-card">
                <span>Incident Records</span>
                <strong>{{ number_format($summary['incidents']) }}</strong>
            </div>
            <div class="stat-card">
                <span>Total Estimated Damage</span>
                <strong>₱{{ number_format($summary['total_damage'], 2) }}</strong>
            </div>
            <div class="stat-card">
                <span>Average per Incident</span>
                <strong>₱{{ number_format($summary['average_damage'], 2) }}</strong>
            </div>
        </section>

        <h2>Damage by Barangay</h2>
        <table>
            <thead>
                <tr>
                    <th>Barangay</th>
                    <th class="num">Affected MSMEs</th>
                    <th class="num">Incidents</th>
                    <th class="num">Estimated Damage</th>
                    <th class="num">Share</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['by_barangay'] as $row)
                    <tr>
                        <td>{{ $row['barangay'] }}</td>
                        <td class="num">{{ number_format($row['affected']) }}</td>
                        <td class="num">{{ number_format($row['incidents']) }}</td>
                        <td class="num">₱{{ number_format($row['damage'], 2) }}</td>
                        <td class="num">{{ number_format($row['share'], 1) }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty">No incidents recorded for this calamity.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($report['by_barangay'] !== [])
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="num">{{ number_format($summary['affected']) }}</td>
                        <td class="num">{{ number_format($summary['incidents']) }}</td>
                        <td class="num">₱{{ number_format($summary['total_damage'], 2) }}</td>
                        <td class="num">100.0%</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <div class="two-col">
            <section>
                <h2>Damage by Status</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th class="num">Incidents</th>
                            <th class="num">Damage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['by_status'] as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td class="num">{{ number_format($row['count']) }}</td>
                                <td class="num">₱{{ number_format($row['damage'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <section>
                <h2>Damage by Classification</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Classification</th>
                            <th class="num">Incidents</th>
                            <th class="num">Damage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['by_nature'] as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td class="num">{{ number_format($row['count']) }}</td>
                                <td class="num">₱{{ number_format($row['damage'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        </div>

        <h2>Affected MSMEs</h2>
        <table class="details">
            <thead>
                <tr>
                    <th>#</th>
                    <th>SCIMS Entity No.</th>
                    <th>MSME Name</th>
                    <th>Barangay</th>
                    <th>Date Occurred</th>
                    <th>Nature</th>
                    <th class="num">Estimated Cost</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($report['incidents'] as $incident)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $incident->juridical_id }}</td>
                        <td>{{ $incident->juridical_name ?? $incident->juridical_id }}</td>
                        <td>{{ $incident->barangay ?: 'Unspecified' }}</td>
                        <td>{{ $incident->date_occurred ? \Illuminate\Support\Carbon::parse($incident->date_occurred)->format('M d, Y') : '—' }}</td>
                        <td>{{ \App\Models\CalamityMonitoring\Incident::natureLabel($incident->nature_of_damage) }}</td>
                        <td class="num">₱{{ number_format((float) $incident->estimated_cost_of_damages, 2) }}</td>
                        <td>{{ \App\Models\CalamityMonitoring\Incident::statusLabel($incident->status) }}</td>
                        <td>{{ $incident->remarks ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty">No incidents recorded for this calamity.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($report['incidents']->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="6">Total Estimated Damage</td>
                        <td class="num">₱{{ number_format($summary['total_damage'], 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>

        @php
            $photoIncidents = $report['incidents']
                ->filter(fn ($incident): bool => filled($incident->photos));
        @endphp

        @if ($photoIncidents->isNotEmpty())
            <h2>Photo Evidence</h2>

            @foreach ($photoIncidents as $incident)
                @php
                    $photos = collect($incident->photos)
                        ->filter(fn ($photo): bool => \Illuminate\Support\Facades\Storage::disk('public')->exists($photo));
                @endphp

                @if ($photos->isNotEmpty())
                    <section class="photo-group">
                        <div class="photo-caption">
                            <strong>{{ $incident->juridical_name ?? $incident->juridical_id }}</strong>
                            <span>{{ $incident->barangay ?: 'Unspecified' }} &middot; {{ $incident->juridical_id }}</span>
                        </div>

                        <div class="photo-grid">
                            @foreach ($photos as $photo)
                                <figure class="photo-item">
                                    <img
                                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($photo) }}"
                                        alt="Photo evidence for {{ $incident->juridical_name ?? $incident->juridical_id }}"
                                    >
                                </figure>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
        @endif

        <section class="signatures">
            <div class="signature">
                <span>Prepared by</span>
                <div class="line"></div>
                <small>Name / Designation</small>
            </div>
            <div class="signature">
                <span>Verified by</span>
                <div class="line"></div>
                <small>Name / Designation</small>
            </div>
            <div class="signature">
                <span>Approved by</span>
                <div class="line"></div>
                <small>Name / Designation</small>
            </div>
        </section>

        <footer class="report-footer">
            Generated from NegosyoTrack — Negosyo Center, City of San Carlos.
            MSME names and barangays are snapshotted from the SCIMS registry at the time each incident was recorded.
        </footer>
    </main>
</body>
</html>
