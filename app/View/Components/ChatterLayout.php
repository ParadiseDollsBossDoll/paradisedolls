<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class ChatterLayout extends Component
{
    public function __construct(
        public ?string $title = null,
        public bool $hideSidebar = false,
        public bool $player = false,
    ) {}

    public function render(): View
    {
        return view('layouts.chatter');
    }
}
