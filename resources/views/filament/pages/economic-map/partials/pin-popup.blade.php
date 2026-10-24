// Shared business pin popup used by every economic map view.
// Inline colours are intentional: MapLibre's popup background is always
// white, while Filament's dark theme makes inherited text white.
function emapPinPopupHtml(p, showExactPin = false) {
    const row = (label, value, mono = false) =>
        `<p style="margin:0;padding:1px 0;color:#374151;">` +
        `<span style="font-weight:600;color:#6b7280;">${label}</span> ` +
        `<span style="color:#111827;${mono ? 'font-family:ui-monospace,SFMono-Regular,Menlo,monospace;' : ''}">${escapeHtml(value || '—')}</span>` +
        `</p>`;

    const industry = p.industry || p.sector || '—';
    const color = emapPinColor(p.color);

    const exactPin = showExactPin
        ? `<p style="margin:6px 0 0;padding-top:6px;border-top:1px solid #e5e7eb;font-size:10px;line-height:1.4;color:#9ca3af;">` +
          `Exact pin: <span style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;">${Number(p.lat).toFixed(8)}, ${Number(p.lng).toFixed(8)}</span>` +
          `</p>`
        : '';

    return `<div style="min-width:210px;max-width:280px;white-space:normal;background:#fff;color:#111827;font-size:12px;line-height:1.5;">` +
        `<p style="margin:0 0 6px;padding:0 0 6px;border-bottom:1px solid #e5e7eb;font-size:13px;font-weight:700;color:#111827;">${escapeHtml(p.name || '—')}</p>` +
        row('Entity No.', p.entity_no, true) +
        row('Location', p.street) +
        row('Barangay', p.barangay) +
        `<p style="margin:0;padding:1px 0;color:#374151;">` +
        `<span style="font-weight:600;color:#6b7280;">Industry</span> ` +
        `<span style="display:inline-block;width:8px;height:8px;border-radius:9999px;vertical-align:middle;background:${escapeHtml(color)};"></span> ` +
        `<span style="color:#111827;">${escapeHtml(industry)}</span>` +
        `</p>` +
        exactPin +
        `</div>`;
}
