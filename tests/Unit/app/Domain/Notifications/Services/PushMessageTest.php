<?php

namespace Unit\app\Domain\Notifications\Services;

use Leantime\Domain\Notifications\Services\Push;
use Unit\TestCase;

/**
 * The per-platform FCM message: Android gets a data-only message the app renders
 * with action buttons; iOS gets APNs fields for buttons (category), grouping
 * (thread-id), quiet delivery (interruption-level) and summary ranking
 * (relevance-score). Tier and category default sensibly when a caller omits them.
 */
class PushMessageTest extends TestCase
{
    public function test_tier_defaults_to_quiet_and_category_follows_the_module(): void
    {
        $this->assertSame(['module' => 'tickets', 'tier' => '2', 'category' => 'task'], Push::withDefaults(['module' => 'tickets']));
        $this->assertSame('comment', Push::withDefaults(['module' => 'comments'])['category']);
        $this->assertSame('none', Push::withDefaults(['module' => 'goalcanvas'])['category']);
        $this->assertSame('none', Push::withDefaults([])['category']);

        $this->assertSame('1', Push::withDefaults(['tier' => '1'])['tier']);
        $this->assertSame('2', Push::withDefaults(['tier' => '7'])['tier'], 'anything but 1 is quiet');
        $this->assertSame('task', Push::withDefaults(['module' => 'comments', 'category' => 'task'])['category'], 'an explicit valid category wins');
        $this->assertSame('comment', Push::withDefaults(['module' => 'comments', 'category' => 'bogus'])['category'], 'an unknown category is re-derived');
    }

    public function test_ios_tier_one_assignment_has_buttons_sound_and_top_relevance(): void
    {
        $data = ['module' => 'tickets', 'moduleId' => '42', 'type' => 'assigned', 'threadId' => 'ticket-42', 'tier' => '1', 'category' => 'task'];
        $message = Push::fcmMessage('ios', 'Fix login', 'Sam assigned this to you', $data);

        $this->assertSame(['title' => 'Fix login', 'body' => 'Sam assigned this to you'], $message['notification']);
        $this->assertSame($data, $message['data']);
        $this->assertSame([
            'interruption-level' => 'active',
            'relevance-score' => 1.0,
            'sound' => 'default',
            'thread-id' => 'ticket-42',
            'category' => 'leantime-task',
        ], $message['apns']['payload']['aps'], "tier 1 plays a sound; 'active' alone is silent");
        $this->assertArrayNotHasKey('android', $message);
    }

    public function test_ios_tier_two_is_passive_with_low_relevance_and_no_buttons_without_a_category(): void
    {
        $message = Push::fcmMessage('ios', 'Goal', 'x', ['module' => 'goalcanvas', 'moduleId' => '5']);
        $aps = $message['apns']['payload']['aps'];

        $this->assertSame('passive', $aps['interruption-level']);
        $this->assertSame(0.2, $aps['relevance-score']);
        $this->assertArrayNotHasKey('sound', $aps, 'quiet means no sound');
        $this->assertArrayNotHasKey('category', $aps, "'none' registers no buttons");
        $this->assertArrayNotHasKey('thread-id', $aps);
        $this->assertSame('2', $message['data']['tier']);
    }

    public function test_relevance_is_half_for_other_tier_one_and_full_for_mentions_and_replies(): void
    {
        $score = fn (array $data) => Push::fcmMessage('ios', 't', 'b', $data)['apns']['payload']['aps']['relevance-score'];

        $this->assertSame(0.5, $score(['module' => 'comments', 'type' => 'commented', 'tier' => '1']));
        $this->assertSame(1.0, $score(['module' => 'comments', 'type' => 'mentioned', 'tier' => '1']));
        $this->assertSame(1.0, $score(['module' => 'comments', 'type' => 'replied', 'tier' => '1']));
        $this->assertSame(0.2, $score(['module' => 'comments', 'type' => 'mentioned', 'tier' => '2']), 'tier decides before type');
    }

    public function test_android_gets_a_data_only_message_the_app_renders(): void
    {
        $message = Push::fcmMessage('android', 'Fix login', 'Sam: looks good', ['module' => 'comments', 'moduleId' => '4', 'parentTicketId' => '42', 'tier' => '1']);

        $this->assertArrayNotHasKey('notification', $message, 'the system tray would draw it without buttons');
        $this->assertArrayNotHasKey('apns', $message);
        $this->assertSame(['priority' => 'high'], $message['android']);
        $this->assertSame('Fix login', $message['data']['title']);
        $this->assertSame('Sam: looks good', $message['data']['body']);
        $this->assertSame('leantime-direct', $message['data']['channelId']);
        $this->assertSame('comment', $message['data']['category']);
        $this->assertSame('42', $message['data']['parentTicketId']);

        $quiet = Push::fcmMessage('android', 't', 'b', ['module' => 'tickets']);
        $this->assertSame('leantime-quiet', $quiet['data']['channelId']);
        foreach ($quiet['data'] as $value) {
            $this->assertIsString($value, 'FCM data values must be strings');
        }
    }

    public function test_unknown_platforms_get_the_ios_shape(): void
    {
        $message = Push::fcmMessage('', 't', 'b', ['module' => 'tickets', 'threadId' => 'ticket-1']);

        $this->assertArrayHasKey('notification', $message);
        $this->assertSame('ticket-1', $message['apns']['payload']['aps']['thread-id']);
        $this->assertSame('leantime-task', $message['apns']['payload']['aps']['category']);
    }
}
