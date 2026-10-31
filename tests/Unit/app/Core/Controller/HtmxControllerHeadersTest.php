<?php

namespace Unit\app\Core\Controller;

use Leantime\Core\Controller\HtmxController;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * Headers queued during an htmx action (HX-Trigger events, notifications) reach the browser
 * even when the action builds its own Response instead of rendering the controller's view.
 */
class HtmxControllerHeadersTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_display_raw_carries_the_events_queued_on_the_template(): void
    {
        $template = $this->templateWithQueuedEvents(['lt:ui:notify']);

        $response = $template->displayRaw('Saved title');

        $this->assertSame('lt:ui:notify', $response->headers->get('HX-Trigger'));
        $this->assertSame('text/plain; charset=utf-8', $response->headers->get('Content-Type'));
    }

    public function test_display_json_carries_the_events_queued_on_the_template(): void
    {
        $template = $this->templateWithQueuedEvents(['lt:projects:favorite.added']);

        $response = $template->displayJson(['ok' => true]);

        $this->assertSame('lt:projects:favorite.added', $response->headers->get('HX-Trigger'));
    }

    public function test_action_returning_a_template_response_keeps_template_and_controller_events(): void
    {
        $controller = $this->controller($this->templateWithQueuedEvents(['lt:ui:notify']));

        $response = $controller->callAction('rawTitle', []);

        $this->assertSame('lt:ui:notify,lt:projects:favorite.added', $response->headers->get('HX-Trigger'));
    }

    public function test_action_returning_a_hand_built_response_merges_its_own_trigger_with_queued_events(): void
    {
        $controller = $this->controller($this->templateWithQueuedEvents(['lt:ui:notify']));

        $response = $controller->callAction('handBuilt', []);

        $this->assertSame('true', $response->headers->get('HX-Refresh'));
        $this->assertSame('lt:chip:changed,lt:projects:favorite.added,lt:ui:notify', $response->headers->get('HX-Trigger'));
    }

    public function test_a_comma_separated_trigger_set_on_the_template_is_not_doubled(): void
    {
        $template = $this->templateWithQueuedEvents([]);
        $template->setResponseHeader('HX-Trigger', 'lt:ui:notify,lt:chip:changed');

        $response = $this->controller($template)->callAction('rawTitle', []);

        $this->assertSame('lt:ui:notify,lt:chip:changed,lt:projects:favorite.added', $response->headers->get('HX-Trigger'));
    }

    public function test_a_json_trigger_set_by_hand_is_left_alone(): void
    {
        $controller = $this->controller($this->templateWithQueuedEvents(['lt:ui:notify']));

        $response = $controller->callAction('jsonTrigger', []);

        $this->assertSame('{"showMessage":"hi"}', $response->headers->get('HX-Trigger'));
    }

    public function test_action_returning_a_fragment_key_is_passed_through(): void
    {
        $controller = $this->controller($this->templateWithQueuedEvents([]));

        $this->assertSame('row', $controller->callAction('fragmentKey', []));
    }

    /**
     * @param  array<int, string>  $events
     */
    private function templateWithQueuedEvents(array $events): Template
    {
        $headers = empty($events) ? [] : ['HX-Trigger' => $events];

        return $this->make(Template::class, ['headers' => $headers]);
    }

    private function controller(Template $template): HeaderTestController
    {
        $request = IncomingRequest::create('/hx/test/headers/save', 'POST', [], [], [], ['HTTP_HX_REQUEST' => 'true']);

        return new HeaderTestController($request, $template, $this->make(Language::class));
    }
}

class HeaderTestController extends HtmxController
{
    protected static string $view = 'tickets::partials.subtasks';

    public function rawTitle(): Response
    {
        $this->setHTMXEvent('lt:projects:favorite.added');

        return $this->tpl->displayRaw('Saved title');
    }

    public function handBuilt(): Response
    {
        $this->setHTMXEvent('lt:projects:favorite.added');
        $this->headers['HX-Refresh'] = 'true';

        return new Response('', 200, ['HX-Trigger' => 'lt:chip:changed']);
    }

    public function jsonTrigger(): Response
    {
        return new Response('', 200, ['HX-Trigger' => '{"showMessage":"hi"}']);
    }

    public function fragmentKey(): string
    {
        return 'row';
    }
}
