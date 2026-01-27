<?php

declare(strict_types=1);

namespace App\View\Components\Racing;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class PaddockMapView extends Component
{
    public function __construct(
        public Collection $spots,
        public ?int $selectedSpotId = null,
        public ?int $highlightSpotId = null,
        public int $width = 1200,
        public int $height = 800,
        public bool $interactive = true,
        public string $emptyMessage = 'Aucun emplacement positionné sur la carte',
    ) {}

    public function render(): View
    {
        return view('components.racing.paddock-map-view');
    }
}
