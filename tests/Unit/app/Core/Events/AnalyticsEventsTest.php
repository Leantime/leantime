<?php

namespace Unit\app\Core\Events;

use Leantime\Core\Events\Contracts\LeantimeEvent;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Api\Events\ApiKeyCreated;
use Leantime\Domain\Auth\Events\LoginSucceeded;
use Leantime\Domain\Auth\Events\OnboardingCompleted;
use Leantime\Domain\Auth\Events\UserRegistered;
use Leantime\Domain\Blueprints\Events\CanvasCreated;
use Leantime\Domain\Blueprints\Events\CanvasItemCreated;
use Leantime\Domain\Comments\Events\CommentAdded;
use Leantime\Domain\Files\Events\FileUploaded;
use Leantime\Domain\Plugins\Events\PluginDisabled;
use Leantime\Domain\Plugins\Events\PluginEnabled;
use Leantime\Domain\Plugins\Events\PluginInstalled;
use Leantime\Domain\Projects\Events\ProjectArchived;
use Leantime\Domain\Projects\Events\ProjectCreated;
use Leantime\Domain\Projects\Events\ProjectMemberAdded;
use Leantime\Domain\Tickets\Events\TicketCompleted;
use Leantime\Domain\Timesheets\Events\TimeEntryCreated;
use Leantime\Domain\Timesheets\Events\TimerStarted;
use Leantime\Domain\TwoFA\Events\TwoFactorEnabled;
use Leantime\Domain\Users\Events\InviteSent;
use Leantime\Domain\Users\Events\UserCreated;
use Leantime\Domain\Wiki\Events\WikiArticleCreated;
use Unit\TestCase;

/**
 * The class-based events consumed by analytics plugins (e.g. PostHog). Their FQCNs and payload
 * property names/types are a contract with those plugins, so this pins them, and covers the
 * namespace-wildcard subscription such a plugin uses to receive every domain event.
 */
class AnalyticsEventsTest extends TestCase
{
    private array $staticSnapshot = [];

    private const STATIC_PROPS = [
        'eventRegistry',
        'filterRegistry',
        'available_hooks',
        'patternMatchCache',
        'compiledPatternCache',
        'eventRegistryVersion',
        'filterRegistryVersion',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach (self::STATIC_PROPS as $prop) {
            $this->staticSnapshot[$prop] = $reflection->getProperty($prop)->getValue();
        }
    }

    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach ($this->staticSnapshot as $prop => $value) {
            $reflection->getProperty($prop)->setValue(null, $value);
        }

        parent::tearDown();
    }

    /**
     * Each event with constructor arguments and the exact payload contract: property name => type.
     *
     * @return array<string, array{0: class-string<LeantimeEvent>, 1: array<string, mixed>, 2: array<string, string>}>
     */
    public static function analyticsEvents(): array
    {
        return [
            'UserRegistered' => [UserRegistered::class, ['userId' => 1], ['userId' => 'int']],
            'LoginSucceeded' => [LoginSucceeded::class, ['userId' => 1, 'method' => 'password'], ['userId' => 'int', 'method' => 'string']],
            'OnboardingCompleted' => [OnboardingCompleted::class, ['userId' => 1], ['userId' => 'int']],
            'UserCreated' => [UserCreated::class, ['userId' => 1, 'role' => 20, 'source' => 'admin'], ['userId' => 'int', 'role' => 'int', 'source' => 'string']],
            'InviteSent' => [InviteSent::class, ['userId' => 1], ['userId' => 'int']],
            'ProjectCreated' => [ProjectCreated::class, ['projectId' => 3, 'type' => 'project'], ['projectId' => 'int', 'type' => 'string']],
            'ProjectArchived' => [ProjectArchived::class, ['projectId' => 3], ['projectId' => 'int']],
            'ProjectMemberAdded' => [ProjectMemberAdded::class, ['projectId' => 3, 'userId' => 1], ['projectId' => 'int', 'userId' => 'int']],
            'TicketCompleted' => [TicketCompleted::class, ['ticketId' => 5, 'projectId' => null], ['ticketId' => 'int', 'projectId' => '?int']],
            'CommentAdded' => [CommentAdded::class, ['commentId' => 9, 'module' => 'ticket', 'moduleId' => 5, 'projectId' => 3], ['commentId' => 'int', 'module' => 'string', 'moduleId' => 'int', 'projectId' => '?int']],
            'TimeEntryCreated' => [TimeEntryCreated::class, ['ticketId' => 5, 'hours' => 1.5, 'projectId' => 3], ['ticketId' => '?int', 'hours' => 'float', 'projectId' => '?int']],
            'TimerStarted' => [TimerStarted::class, ['ticketId' => 5], ['ticketId' => 'int']],
            'CanvasCreated' => [CanvasCreated::class, ['canvasId' => 7, 'type' => 'swotcanvas', 'projectId' => 3], ['canvasId' => 'int', 'type' => 'string', 'projectId' => '?int']],
            'CanvasItemCreated' => [CanvasItemCreated::class, ['canvasItemId' => 8, 'type' => 'goalcanvas', 'projectId' => 3], ['canvasItemId' => 'int', 'type' => 'string', 'projectId' => '?int']],
            'WikiArticleCreated' => [WikiArticleCreated::class, ['articleId' => 4, 'projectId' => 3], ['articleId' => 'int', 'projectId' => '?int']],
            'FileUploaded' => [FileUploaded::class, ['fileId' => 2, 'module' => 'ticket', 'moduleId' => 5, 'extension' => 'pdf', 'size' => 1024], ['fileId' => 'int', 'module' => 'string', 'moduleId' => '?int', 'extension' => 'string', 'size' => 'int']],
            'PluginInstalled' => [PluginInstalled::class, ['plugin' => 'PostHog', 'format' => 'folder'], ['plugin' => 'string', 'format' => 'string']],
            'PluginEnabled' => [PluginEnabled::class, ['plugin' => 'PostHog'], ['plugin' => 'string']],
            'PluginDisabled' => [PluginDisabled::class, ['plugin' => 'PostHog'], ['plugin' => 'string']],
            'TwoFactorEnabled' => [TwoFactorEnabled::class, ['userId' => 1], ['userId' => 'int']],
            'ApiKeyCreated' => [ApiKeyCreated::class, ['apiUserId' => 12], ['apiUserId' => 'int']],
        ];
    }

    /**
     * Every analytics event is a final LeantimeEvent without legacy string names, constructible
     * with its contract payload, and exposes exactly the contract's public readonly properties.
     *
     * @param  class-string<LeantimeEvent>  $class
     * @param  array<string, mixed>  $arguments
     * @param  array<string, string>  $contract
     *
     * @dataProvider analyticsEvents
     */
    public function test_event_matches_the_analytics_contract(string $class, array $arguments, array $contract): void
    {
        $event = new $class(...$arguments);

        $this->assertInstanceOf(LeantimeEvent::class, $event);
        $this->assertSame([], $event->legacyHooks(), 'analytics events never fired before, so they have no legacy names');
        $this->assertTrue((new \ReflectionClass($class))->isFinal());

        $properties = [];
        foreach ((new \ReflectionClass($class))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $this->assertTrue($property->isReadOnly(), "$class::\${$property->getName()} must be readonly");
            $properties[$property->getName()] = (string) $property->getType();
        }

        $this->assertSame($contract, $properties);
        $this->assertSame($arguments, get_object_vars($event));
    }

    /**
     * A namespace-wildcard key on the FQCN (how an analytics plugin subscribes to every domain
     * event at once) receives the typed event object, from any domain.
     */
    public function test_namespace_wildcard_listener_receives_typed_class_events(): void
    {
        $received = [];
        EventDispatcher::add_event_listener('Leantime\\Domain\\*\\Events\\*', function ($event) use (&$received) {
            $received[] = $event;
        });

        TicketCompleted::dispatch(ticketId: 5, projectId: 3);
        CommentAdded::dispatch(commentId: 9, module: 'ticket', moduleId: 5, projectId: 3);

        $this->assertCount(2, $received);
        $this->assertInstanceOf(TicketCompleted::class, $received[0]);
        $this->assertSame(5, $received[0]->ticketId);
        $this->assertInstanceOf(CommentAdded::class, $received[1]);
        $this->assertSame('ticket', $received[1]->module);
    }

    /**
     * A wildcard scoped to one domain's namespace only receives that domain's events.
     */
    public function test_domain_scoped_wildcard_listener_ignores_other_domains(): void
    {
        $received = [];
        EventDispatcher::add_event_listener('Leantime\\Domain\\Tickets\\Events\\*', function ($event) use (&$received) {
            $received[] = $event;
        });

        CommentAdded::dispatch(commentId: 9, module: 'ticket', moduleId: 5, projectId: 3);
        TicketCompleted::dispatch(ticketId: 5, projectId: 3);

        $this->assertCount(1, $received);
        $this->assertInstanceOf(TicketCompleted::class, $received[0]);
    }
}
