function emapDefaultPinColor() {
    return '#1d4ed8';
}

function emapPinColor(value) {
    const color = String(value ?? '').trim().toLowerCase();

    return /^#[0-9a-f]{6}$/.test(color) ? color : emapDefaultPinColor();
}

function emapPinIconName(color) {
    return `msme-pin-${emapPinColor(color).slice(1)}`;
}

function emapPinImage(color) {
    const fill = emapPinColor(color);
    const size = 32;
    const canvas = document.createElement('canvas');
    canvas.width = size;
    canvas.height = size;

    const context = canvas.getContext('2d');
    context.beginPath();
    context.arc(size / 2, size / 2, 12, 0, Math.PI * 2);
    context.fillStyle = fill;
    context.fill();
    context.lineWidth = 4;
    context.strokeStyle = '#ffffff';
    context.stroke();

    const image = context.getImageData(0, 0, size, size);

    return {
        width: size,
        height: size,
        data: new Uint8Array(image.data),
    };
}

function emapPinFeatures(features) {
    const list = Array.isArray(features) ? features : (features?.features ?? []);

    return list.map((feature) => {
        const color = emapPinColor(feature.properties?.color);

        return {
            ...feature,
            properties: {
                ...feature.properties,
                color,
                icon: emapPinIconName(color),
            },
        };
    });
}

function emapEnsurePinImages(map, features) {
    const images = new Map();

    for (const feature of features) {
        images.set(feature.properties.icon, feature.properties.color);
    }

    for (const [name, color] of images) {
        if (! map.hasImage(name)) {
            map.addImage(name, emapPinImage(color), { pixelRatio: 2 });
        }
    }
}

function emapPinAnchorFeatures(features) {
    const seen = new Set();

    return features.filter((feature) => {
        const lat = Number(feature.properties?.lat);
        const lng = Number(feature.properties?.lng);
        const key = `${lat.toFixed(6)},${lng.toFixed(6)}`;

        if (seen.has(key)) return false;
        seen.add(key);

        return true;
    }).map((feature) => ({
        type: 'Feature',
        properties: {},
        geometry: feature.geometry,
    }));
}

function emapAddPinLayer(map, features) {
    const pins = emapPinFeatures(features);
    emapEnsurePinImages(map, pins);

    // Small dot at the exact saved coordinate. It stays hidden beneath a
    // lone pin and marks the true point when pins are fanned out.
    map.addSource('pin-anchors', {
        type: 'geojson',
        data: { type: 'FeatureCollection', features: emapPinAnchorFeatures(pins) },
    });
    map.addLayer({
        id: 'pin-anchors',
        type: 'circle',
        source: 'pin-anchors',
        paint: {
            'circle-radius': 2,
            'circle-color': '#4b5563',
            'circle-opacity': 0.7,
            'circle-stroke-width': 1,
            'circle-stroke-color': '#ffffff',
        },
    });

    map.addSource('pins', {
        type: 'geojson',
        data: { type: 'FeatureCollection', features: pins },
    });
    map.addLayer({
        id: 'pins',
        type: 'symbol',
        source: 'pins',
        layout: {
            // One image per industry colour, selected by the feature's icon.
            'icon-image': ['get', 'icon'],
            'icon-size': 1,
            'icon-anchor': 'center',
            // Data-driven screen-space offset; geometry stays exact.
            'icon-offset': ['get', 'offset'],
            'icon-allow-overlap': true,
            'icon-ignore-placement': true,
        },
    });
}

function emapRenderPins(map, features) {
    const pins = emapPinFeatures(features);
    emapEnsurePinImages(map, pins);

    map.getSource('pin-anchors')?.setData({
        type: 'FeatureCollection',
        features: emapPinAnchorFeatures(pins),
    });
    map.getSource('pins')?.setData({
        type: 'FeatureCollection',
        features: pins,
    });
}
