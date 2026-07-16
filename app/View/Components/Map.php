<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Map extends Component
{
    public function __construct(
        public string $id = 'map',
        public ?float $lat = null,
        public ?float $lng = null,
        public int $zoom = 14,
        public bool $readOnly = false,
        public string $height = '400px',
        public ?string $className = null,
        public array $markers = [],
        public ?string $centerMarkerLabel = null,
    ) {
        if ($this->lat === null) {
            $this->lat = (float) config('maps.default_lat', -6.2088);
        }
        if ($this->lng === null) {
            $this->lng = (float) config('maps.default_lng', 106.8456);
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.map');
    }
}
