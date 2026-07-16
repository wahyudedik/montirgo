{{-- Leaflet.js Map Component --}}
@props(['id', 'lat', 'lng', 'zoom', 'readOnly', 'height', 'className', 'markers', 'centerMarkerLabel'])

<div
    id="{{ $id }}"
    class="rounded-2xl overflow-hidden border border-gray-200 {{ $className ?? '' }}"
    style="height: {{ $height }}; width: 100%;"
    x-data="mapComponent('{{ $id }}', {{ $lat }}, {{ $lng }}, {{ $zoom }}, {{ $readOnly ? 'true' : 'false' }})"
    x-init="initMap()"
>
    {{-- Marker data --}}
    <template x-if="true">
        <div
            x-data='{ markers: @json($markers) }'
            x-effect="if (markers.length > 0) updateMarkers(markers)"
        ></div>
    </template>
</div>

@once
    {{-- Leaflet.js CSS & JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @push('scripts')
    <script>
        function mapComponent(id, defaultLat, defaultLng, defaultZoom, readOnly) {
            return {
                map: null,
                centerMarker: null,
                markerLayers: [],

                initMap() {
                    this.map = L.map(id, {
                        zoomControl: !readOnly,
                        dragging: !readOnly,
                        doubleClickZoom: !readOnly,
                        scrollWheelZoom: !readOnly,
                    }).setView([defaultLat, defaultLng], defaultZoom);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                        maxZoom: 19,
                    }).addTo(this.map);

                    // Center marker with label
                    @if($centerMarkerLabel)
                        this.centerMarker = L.marker([defaultLat, defaultLng])
                            .addTo(this.map)
                            .bindPopup('{{ $centerMarkerLabel }}')
                            .openPopup();
                    @elseif(!$readOnly)
                        this.centerMarker = L.marker([defaultLat, defaultLng], {
                            draggable: true,
                        }).addTo(this.map);

                        this.centerMarker.on('dragend', (e) => {
                            const pos = e.target.getLatLng();
                            this.$dispatch('map-marker-moved', {
                                lat: pos.lat.toFixed(7),
                                lng: pos.lng.toFixed(7),
                            });
                        });
                    @else
                        this.centerMarker = L.marker([defaultLat, defaultLng]).addTo(this.map);
                    @endif

                    // Fix map rendering in modals/tabs
                    setTimeout(() => {
                        this.map.invalidateSize();
                    }, 300);
                },

                updateCenter(lat, lng, label) {
                    this.map.setView([lat, lng], this.map.getZoom());
                    if (this.centerMarker) {
                        this.centerMarker.setLatLng([lat, lng]);
                        if (label) {
                            this.centerMarker.bindPopup(label).openPopup();
                        }
                    }
                },

                updateMarkers(markersData) {
                    // Clear existing markers
                    this.markerLayers.forEach(m => this.map.removeLayer(m));
                    this.markerLayers = [];

                    markersData.forEach(m => {
                        const marker = L.marker([m.lat, m.lng]).addTo(this.map);
                        if (m.label) {
                            marker.bindPopup(m.label);
                        }
                        if (m.color) {
                            marker.setIcon(L.divIcon({
                                className: 'custom-marker',
                                html: `<div style="background:${m.color};width:12px;height:12px;border-radius:50%;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.3);"></div>`,
                                iconSize: [16, 16],
                                iconAnchor: [8, 8],
                            }));
                        }
                        this.markerLayers.push(marker);
                    });

                    // Fit bounds if multiple markers
                    if (markersData.length > 1) {
                        const group = L.featureGroup(this.markerLayers);
                        this.map.fitBounds(group.getBounds().pad(0.2));
                    }
                },

                setView(lat, lng, zoom) {
                    this.map.setView([lat, lng], zoom || this.map.getZoom());
                },
            };
        }
    </script>
    @endpush
@endonce
