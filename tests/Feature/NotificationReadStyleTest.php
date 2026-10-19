<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SystemNotification;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationReadStyleTest extends TestCase
{
    use RefreshDatabase;

    public function test_read_titles_are_regular_and_unread_titles_are_bold_in_both_views(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $user->notifyNow(new SystemNotification('Unread example', 'New update', '/admin'));
        $user->notifyNow(new SystemNotification('Read example', 'Previous update', '/admin'));
        $user->notifications()->where('data->title', 'Read example')->firstOrFail()->markAsRead();
        $this->actingAs($user);

        $views = [
            view('layouts.partials.notification-bell')->render(),
            $this->get(route('notifications.index'))->assertOk()->getContent(),
        ];
        foreach ($views as $html) {
            $dom = new DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
            $xpath = new DOMXPath($dom);
            foreach (['Read example' => 'font-normal', 'Unread example' => 'font-semibold'] as $title => $weight) {
                $nodes = $xpath->query('//p[normalize-space(text())="'.$title.'"]');
                $this->assertGreaterThan(0, $nodes->length);
                foreach ($nodes as $node) {
                    $this->assertContains($weight, explode(' ', $node->getAttribute('class')));
                }
            }
        }
    }
}
