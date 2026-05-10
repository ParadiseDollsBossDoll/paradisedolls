<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class MemberLayout extends Component
{
    public function __construct(public bool $hideSidebar = false)
    {
    }

    public function render(): View
    {
        return view('layouts.member');
    }
}
