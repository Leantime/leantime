<?php

namespace Unit\app\Core\Events;

use Leantime\Core\Events\Htmx\HtmxEvents;
use Leantime\Core\Events\Htmx\HtmxUiEvents;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\UI\Template;
use Leantime\Domain\Tickets\Events\SubtasksUpdated;
use Leantime\Domain\Tickets\Events\TicketUpdated;
use Unit\TestCase;

/**
 * Domain events that implement EmitsClientEvent put their client event names on the
 * HX-Trigger header of htmx responses — and only of htmx responses.
 */
class ClientEventsTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, string> Event names the stub template was asked to emit. */
    private array $emitted = [];

    private mixed $originalRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalRequest = app('request');
        $this->emitted = [];

        $template = $this->make(Template::class, [
            'emit' => function (...$events) {
                array_push($this->emitted, ...$events);
            },
        ]);
        app()->instance(Template::class, $template);
    }

    protected function tearDown(): void
    {
        app()->instance('request', $this->originalRequest);
        app()->forgetInstance(Template::class);

        parent::tearDown();
    }

    public function test_dispatch_during_htmx_request_queues_broad_and_scoped_name(): void
    {
        $this->useRequest(htmx: true);

        TicketUpdated::dispatch(ticketId: 42);

        $this->assertSame(['lt:tickets:ticket.updated', 'lt:tickets:ticket.updated#42'], $this->emitted);
    }

    public function test_bulk_update_without_ticket_queues_only_broad_name(): void
    {
        $this->useRequest(htmx: true);

        TicketUpdated::dispatch();

        $this->assertSame(['lt:tickets:ticket.updated'], $this->emitted);
    }

    public function test_subtasks_updated_is_scoped_to_the_parent_ticket(): void
    {
        $this->useRequest(htmx: true);

        SubtasksUpdated::dispatch(parentTicketId: 7);

        $this->assertSame(['lt:tickets:subtask.updated', 'lt:tickets:subtask.updated#7'], $this->emitted);
    }

    public function test_dispatch_outside_htmx_request_queues_nothing(): void
    {
        $this->useRequest(htmx: false);

        TicketUpdated::dispatch(ticketId: 42);

        $this->assertSame([], $this->emitted);
    }

    public function test_name_resolves_event_classes_enum_cases_and_raw_strings(): void
    {
        $this->assertSame('lt:tickets:ticket.updated', HtmxEvents::name(TicketUpdated::class));
        $this->assertSame('lt:ui:notify', HtmxEvents::name(HtmxUiEvents::Notify));
        $this->assertSame('timerUpdate', HtmxEvents::name('timerUpdate'));
        $this->assertSame('lt:tickets:subtask.updated#9', HtmxEvents::scoped(SubtasksUpdated::class, 9));
    }

    public function test_trigger_header_accepts_event_classes_and_keeps_legacy_aliases(): void
    {
        $header = HtmxEvents::triggerHeader([TicketUpdated::class]);

        $this->assertSame('lt:tickets:ticket.updated,ticket_update', $header);
    }

    private function useRequest(bool $htmx): void
    {
        $server = $htmx ? ['HTTP_HX_REQUEST' => 'true'] : [];
        app()->instance('request', IncomingRequest::create('/hx/tickets/subtasks/save', 'POST', [], [], [], $server));
    }
}
