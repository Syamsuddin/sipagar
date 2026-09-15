<?php

namespace App\View\Components\Layout;

use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/** <x-layout.pdf> → resources/views/layouts/pdf.blade.php (docs/26 §PDF). */
class Pdf extends Component
{
    /** @param array<string, string|null> $pengaturan */
    public function __construct(
        public string $judul,
        public string $subjudul,
        public array $pengaturan,
        public string $printCss,
        public CarbonInterface $dicetak,
    ) {}

    public function render(): View
    {
        return view('layouts.pdf');
    }
}
