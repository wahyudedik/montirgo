<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Map Provider
    |--------------------------------------------------------------------------
    |
    | Peta provider yang digunakan untuk web views.
    | Options: 'leaflet' (free), 'google' (requires API key)
    |
    */
    'provider' => env('MAP_PROVIDER', 'leaflet'),

    /*
    |--------------------------------------------------------------------------
    | Google Maps API Key
    |--------------------------------------------------------------------------
    |
    | API key untuk Google Maps JavaScript API dan Geocoding API.
    | Dapatkan di: https://console.cloud.google.com/apis/credentials
    |
    */
    'google_api_key' => env('GOOGLE_MAPS_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Location (Jakarta)
    |--------------------------------------------------------------------------
    |
    | Posisi default jika lokasi user tidak tersedia.
    |
    */
    'default_lat' => env('MAP_DEFAULT_LAT', '-6.2088'),
    'default_lng' => env('MAP_DEFAULT_LNG', '106.8456'),

    /*
    |--------------------------------------------------------------------------
    | Map Settings
    |--------------------------------------------------------------------------
    |
    | Pengaturan default untuk peta.
    |
    */
    'default_zoom' => (int) env('MAP_DEFAULT_ZOOM', 14),
    'marker_cluster' => (bool) env('MAP_MARKER_CLUSTER', true),

    /*
    |--------------------------------------------------------------------------
    | ETA Settings
    |--------------------------------------------------------------------------
    |
    | Kecepatan rata-rata (km/jam) untuk estimasi waktu tempuh.
    | Diubah berdasarkan jenis kendaraan.
    |
    */
    'avg_speed_kmh' => [
        'motorcycle' => 35,
        'car' => 30,
        'default' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenStreetMap Tile URL
    |--------------------------------------------------------------------------
    |
    | Tile server untuk Leaflet.js (default: OpenStreetMap, gratis).
    |
    */
    'tile_url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    'tile_attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',

];
