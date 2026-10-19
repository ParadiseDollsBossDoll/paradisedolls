<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function __invoke(): View
    {
        return view('marketing.success-stories');
    }
}
