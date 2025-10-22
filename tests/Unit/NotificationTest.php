<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Disable observers for testing
        Notification::unsetEventDispatcher();
    }

    public function test_get_link_attribute_with_cotation_update_type(): void
    {
        $user = User::factory()->create();
        $notification = Notification::create([
            'title' => 'Test',
            'message' => 'Test message',
            'type' => 'info',
            'data' => [
                'type' => 'cotation_update',
                'cotation_update_id' => 123
            ],
            'user_id' => $user->id,
        ]);

        $this->assertStringContainsString('cotation-updates/123', $notification->link);
    }

    public function test_get_link_attribute_without_type(): void
    {
        $user = User::factory()->create();
        $notification = Notification::create([
            'title' => 'Test',
            'message' => 'Test message',
            'type' => 'info',
            'data' => null,
            'user_id' => $user->id,
        ]);

        $this->assertEquals('#', $notification->link);
    }

    public function test_get_link_attribute_with_unknown_type(): void
    {
        $user = User::factory()->create();
        $notification = Notification::create([
            'title' => 'Test',
            'message' => 'Test message',
            'type' => 'info',
            'data' => [
                'type' => 'unknown_type'
            ],
            'user_id' => $user->id,
        ]);

        $this->assertEquals('#', $notification->link);
    }

    public function test_mark_as_read(): void
    {
        $user = User::factory()->create();
        $notification = Notification::create([
            'title' => 'Test',
            'message' => 'Test message',
            'type' => 'info',
            'read' => false,
            'user_id' => $user->id,
        ]);

        $this->assertFalse($notification->read);
        $this->assertNull($notification->read_at);

        $notification->markAsRead();
        $notification->refresh();

        $this->assertTrue($notification->read);
        $this->assertNotNull($notification->read_at);
    }

    public function test_unread_scope(): void
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

        $unreadNotifications = Notification::unread()->get();

        $this->assertCount(1, $unreadNotifications);
        $this->assertEquals('Unread', $unreadNotifications->first()->title);
    }

    public function test_create_for_user(): void
    {
        $user = User::factory()->create();

        $notification = Notification::createForUser(
            $user,
            'Test Title',
            'Test Message',
            'warning',
            ['key' => 'value']
        );

        $this->assertEquals($user->id, $notification->user_id);
        $this->assertEquals('Test Title', $notification->title);
        $this->assertEquals('Test Message', $notification->message);
        $this->assertEquals('warning', $notification->type);
        $this->assertEquals(['key' => 'value'], $notification->data);
    }

    public function test_create_for_all_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Notification::createForAllUsers(
            'Global Title',
            'Global Message',
            'info',
            ['global' => 'data']
        );

        // Check that notifications were created for both users
        $notifications = Notification::all();
        $this->assertCount(2, $notifications);

        foreach ($notifications as $notification) {
            $this->assertEquals('Global Title', $notification->title);
            $this->assertEquals('Global Message', $notification->message);
            $this->assertEquals('info', $notification->type);
            $this->assertEquals(['global' => 'data'], $notification->data);
        }
    }
}
