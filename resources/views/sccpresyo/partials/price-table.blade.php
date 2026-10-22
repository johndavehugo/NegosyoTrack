{{--
    Shared price table partial.
    Props:
      $rows         — collection of Commodity models
      $showCategory — bool, show Category column (for search results)
--}}
<div style="overflow-x:auto;">
    <table class="price-table">
        <thead>
            <tr>
                <th style="width:36px;">#</th>
                <th>Commodity</th>
                @if($showCategory ?? false)
                <th class="col-brand">Category</th>
                @else
                <th class="col-brand">Brand</th>
                @endif
                <th class="col-estab">Establishment</th>
                <th class="col-unit">Unit</th>
                <th>SRP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $c)
            <tr>
                <td style="color:#9CA3AF;font-size:11px;" class="mono">{{ $i + 1 }}</td>

                <td>
                    <div style="font-size:13px;font-weight:600;color:#0D1217;line-height:1.3;">
                        {{ $c->product_name }}
                    </div>
                    @if($c->brand_name)
                    <div style="font-size:11px;color:#9CA3AF;margin-top:2px;" class="col-brand-mobile">{{ $c->brand_name }}</div>
                    @endif
                    @if($c->establishments)
                    <div style="font-size:11px;color:#9CA3AF;margin-top:1px;display:flex;align-items:center;gap:3px;" class="col-estab-mobile">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;opacity:.5;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        {{ $c->establishments }}
                    </div>
                    @endif
                </td>

                @if($showCategory ?? false)
                <td class="col-brand" style="font-size:12px;color:#374151;">
                    {{ $c->category?->name ?? '—' }}
                </td>
                @else
                <td class="col-brand" style="font-size:12px;color:#374151;">
                    {{ $c->brand_name ?: '—' }}
                </td>
                @endif

                <td class="col-estab" style="font-size:12px;color:#374151;">
                    @if($c->establishments)
                        <span style="display:flex;align-items:center;gap:5px;">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            {{ $c->establishments }}
                        </span>
                    @else
                        <span style="color:#D1D5DB;">—</span>
                    @endif
                </td>

                <td class="col-unit">
                    <span style="background:#F3F4F6;border:1px solid #E5E7EB;border-radius:4px;padding:2px 7px;font-size:11px;font-weight:600;color:#6B7280;white-space:nowrap;">
                        {{ $c->unit_of_measure }}
                    </span>
                </td>

                <td>
                    @if($c->srp)
                        <div style="font-size:15px;font-weight:800;color:#0D1217;letter-spacing:-.3px;white-space:nowrap;" class="mono">
                            ₱{{ number_format($c->srp, 2) }}
                        </div>
                        <div style="font-size:10px;color:#9CA3AF;margin-top:1px;">/{{ $c->unit_of_measure }}</div>
                    @else
                        <span style="font-size:11px;color:#D1D5DB;font-style:italic;">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding:32px;text-align:center;color:#9CA3AF;font-size:13px;">No items found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<style>
    /* Hide brand/estab inline labels on larger screens — shown in their own columns */
    .col-brand-mobile { display:block; }
    .col-estab-mobile { display:flex; }
    @media (min-width:600px) { .col-brand-mobile { display:none; } }
    @media (min-width:800px) { .col-estab-mobile { display:none; } }
</style>
