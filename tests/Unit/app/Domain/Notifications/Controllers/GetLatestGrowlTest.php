<?php

namespace Unit\app\Domain\Notifications\Controllers;

use Leantime\Core\UI\Template;
use Leantime\Domain\Notifications\Controllers\GetLatestGrowl;
use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
use Unit\TestCase;

/**
 * The HTMX notification path (lt:ui:notify → /notifications/getLatestGrowl) shows the text as
 * HTML too, so it gets the same rich-text cleaning as the inline script.
 */
class GetLatestGrowlTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function controller(?array $payload): GetLatestGrowl
    {
        $controller = (new \ReflectionClass(GetLatestGrowl::class))->newInstanceWithoutConstructor();
        $controller->init($this->make(NotificationService::class, ['consumeFlashNotification' => fn () => $payload]));

        $tpl = new \ReflectionProperty($controller, 'tpl');
        $tpl->setValue($controller, (new \ReflectionClass(Template::class))->newInstanceWithoutConstructor());

        return $controller;
    }

    public function test_the_notification_is_cleaned_before_it_is_sent(): void
    {
        $response = $this->controller(['notification' => 'Saved <img src=x onerror=alert(1)>', 'type' => "error' onclick='x", 'eventId' => ''])->get();
        $payload = json_decode(json_decode($response->getContent(), true), true);

        $this->assertStringStartsWith('Saved', $payload['notification']);
        $this->assertStringNotContainsString('onerror', $payload['notification']);
        $this->assertSame('erroronclickx', $payload['type']);
    }

    public function test_nothing_pending_stays_false(): void
    {
        $this->assertFalse(json_decode($this->controller(null)->get()->getContent(), true));
    }
}
