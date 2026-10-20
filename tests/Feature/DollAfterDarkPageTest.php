<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DollAfterDarkPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_uses_shared_navigation_and_real_application_link(): void
    {
        $this->get('/doll-after-dark')->assertOk()
            ->assertSee('data-pd-marketing-navbar', false)
            ->assertSee('Doll After Dark')
            ->assertSee('Which Doll are you?')
            ->assertSee('href="'.route('apply').'"', false)
            ->assertSee('APPLY TO THE AGENCY')
            ->assertSee('COURSE COMING SOON')
            ->assertSee('disabled', false)
            ->assertDontSee('JOIN THE COURSE WAITLIST')
            ->assertDontSee('Show layout notes')
            ->assertDontSee('PRIVATE PAGE CONCEPT');
    }
}
