<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Notification;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Disable observers for testing
        User::unsetEventDispatcher();
    }

    public function test_can_access_panel_returns_true(): void
    {
        $user = User::factory()->create();
        $panel = $this->createMock(Panel::class);

        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_notifications_relationship(): void
    {
        $user = User::factory()->create();
        $notification = Notification::create([
            'title' => 'Test',
            'message' => 'Test message',
            'type' => 'info',
            'user_id' => $user->id,
        ]);

        $this->assertCount(1, $user->notifications);
        $this->assertTrue($user->notifications->contains($notification));
    }

    public function test_unread_notifications_scope(): void
    {
        $user = User::factory()->create();

        // Create read notification
        Notification::create([
            'title' => 'Read',
            'message' => 'Read message',
            'type' => 'info',
            'read' => true,
            'user_id' => $user->id,
        ]);

        // Create unread notification
        Notification::create([
            'title' => 'Unread',
            'message' => 'Unread message',
            'type' => 'info',
            'read' => false,
            'user_id' => $user->id,
        ]);

        $this->assertCount(1, $user->unreadNotifications);
        $this->assertEquals('Unread', $user->unreadNotifications->first()->title);
    }

    public function test_unread_notifications_count(): void
    {
        $user = User::factory()->create();

        // Create read notification
        Notification::create([
            'title' => 'Read',
            'message' => 'Read message',
            'type' => 'info',
            'read' => true,
            'user_id' => $user->id,
        ]);

        // Create unread notifications
        Notification::create([
            'title' => 'Unread 1',
            'message' => 'Unread message 1',
            'type' => 'info',
            'read' => false,
            'user_id' => $user->id,
        ]);

        Notification::create([
            'title' => 'Unread 2',
            'message' => 'Unread message 2',
            'type' => 'info',
            'read' => false,
            'user_id' => $user->id,
        ]);

        $this->assertEquals(2, $user->unreadNotificationsCount());
    }
}
