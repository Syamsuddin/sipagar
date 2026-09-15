<?php

namespace App\View\Components\Layout;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * <x-layout.auth> → resources/views/layouts/auth.blade.php (login saja).
 */
class Auth extends Component
{
    public function __construct(public string $title = 'Masuk') {}

    public function render(): View
    {
        return view('layouts.auth');
    }
}
