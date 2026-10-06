<?php

namespace Unit\app\Core;

use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Mailer;

/**
 * App links in notification emails pass through the `notificationEmailUrl` filter so plugins can
 * rewrite them (e.g. add campaign parameters); without listeners the URL is unchanged.
 */
class MailerNotificationEmailUrlTest extends \Unit\TestCase
{
    private array $dispatcherSnapshot = [];

    protected function setUp(): void
    {
        parent::setUp();

        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach (['filterRegistry', 'available_hooks', 'patternMatchCache', 'compiledPatternCache', 'filterRegistryVersion'] as $prop) {
            $this->dispatcherSnapshot[$prop] = $reflection->getProperty($prop)->getValue();
        }
    }

    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach ($this->dispatcherSnapshot as $prop => $value) {
            $reflection->getProperty($prop)->setValue(null, $value);
        }

        parent::tearDown();
    }

    public function test_url_is_unchanged_without_listeners(): void
    {
        $this->assertSame('https://pm.example.com/#/tickets/showTicket/5', Mailer::notificationEmailUrl('https://pm.example.com/#/tickets/showTicket/5', 'tickets'));
    }

    public function test_listener_can_rewrite_the_url_and_receives_the_type(): void
    {
        $receivedType = null;
        EventDispatcher::add_filter_listener('*.notificationEmailUrl', function (string $url, array $params) use (&$receivedType) {
            $receivedType = $params['type'];

            return $url.'?utm_source=notification&utm_campaign='.$params['type'];
        });

        $url = Mailer::notificationEmailUrl('https://pm.example.com/dashboard', 'mention');

        $this->assertSame('https://pm.example.com/dashboard?utm_source=notification&utm_campaign=mention', $url);
        $this->assertSame('mention', $receivedType);
        $this->assertContains('leantime.core.mailer.notificationEmailUrl', EventDispatcher::get_available_hooks()['filters']);
    }

    public function test_an_empty_filter_result_falls_back_to_the_original_url(): void
    {
        EventDispatcher::add_filter_listener('leantime.core.mailer.notificationEmailUrl', fn () => '');

        $this->assertSame('https://pm.example.com/x', Mailer::notificationEmailUrl('https://pm.example.com/x', 'tickets'));
    }
}
