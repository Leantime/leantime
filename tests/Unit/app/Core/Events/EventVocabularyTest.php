<?php

namespace Unit\app\Core\Events;

use Codeception\Test\Unit;
use Leantime\Core\Events\Contracts\EmitsClientEvent;
use Leantime\Core\Events\Contracts\LeantimeEvent;
use Leantime\Core\Events\Contracts\LeantimeFilter;
use Leantime\Core\Events\EventVerb;

/**
 * Enforces the shared event vocabulary across all domains:
 *
 *   - event classes are named {Entity}{Verb} with the verb from the central EventVerb
 *     enum (TicketCreated, MilestoneDeleted — never TicketChanged/TicketEdited)
 *   - filter classes are named {Thing}Filter (TodoWidgetTasksFilter)
 *
 * Scans every class in app/Domain/* /Events and app/Core/* /Events that implements
 * LeantimeEvent or LeantimeFilter. Failing this test means a synonym crept in — use an
 * existing verb or (rarely) add one to EventVerb.
 */
class EventVocabularyTest extends Unit
{
    public function test_event_class_names_end_with_central_vocabulary_verb(): void
    {
        $discovered = $this->discoverEventClasses();

        // Guard against a vacuous pass: if discovery silently finds nothing (e.g. a
        // broken base path), the loop below would assert nothing. The pilot ships ten
        // contract classes in Tickets, so discovery must find them.
        $this->assertContains(
            \Leantime\Domain\Tickets\Events\TicketUpdated::class,
            $discovered,
            'Event class discovery found nothing — the vocabulary check would pass vacuously.'
        );

        $violations = [];

        foreach ($discovered as $class) {
            $implements = class_implements($class);
            $shortName = substr($class, strrpos($class, '\\') + 1);

            if (in_array(LeantimeFilter::class, $implements, true)) {
                if (! str_ends_with($shortName, 'Filter')) {
                    $violations[] = "$class — filter classes must be named {Thing}Filter";
                }

                continue;
            }

            if (in_array(LeantimeEvent::class, $implements, true)) {
                $endsWithVerb = false;
                foreach (EventVerb::cases() as $verb) {
                    if (str_ends_with($shortName, $verb->name)) {
                        $endsWithVerb = true;
                        break;
                    }
                }

                if (! $endsWithVerb) {
                    $violations[] = "$class — event classes must be named {Entity}{Verb} with a verb from EventVerb";
                }
            }
        }

        $this->assertSame([], $violations, "Event vocabulary violations:\n".implode("\n", $violations));
    }

    public function test_client_event_names_follow_the_wire_convention(): void
    {
        $verbs = implode('|', array_map(fn (EventVerb $verb) => $verb->value, EventVerb::cases()));
        $pattern = '/^lt:[a-z]+:[a-z]+\.('.$verbs.')$/';

        $clientEventClasses = array_values(array_filter(
            $this->discoverEventClasses(),
            fn (string $class) => is_subclass_of($class, EmitsClientEvent::class)
        ));

        $this->assertContains(
            \Leantime\Domain\Tickets\Events\TicketUpdated::class,
            $clientEventClasses,
            'Client event discovery found nothing — the wire-name check would pass vacuously.'
        );

        $violations = [];

        foreach ($clientEventClasses as $class) {
            $constant = $class.'::CLIENT_EVENT';

            if (! defined($constant)) {
                $violations[] = "$class — EmitsClientEvent classes must declare a CLIENT_EVENT constant";

                continue;
            }

            if (! preg_match($pattern, constant($constant))) {
                $violations[] = "$class — CLIENT_EVENT '".constant($constant)."' must be lt:{domain}:{entity}.{verb} with a verb from EventVerb";
            }
        }

        $this->assertSame([], $violations, "Client event name violations:\n".implode("\n", $violations));
    }

    /**
     * Finds all classes in Domain and Core Events/ folders that implement one of the
     * class-based hook contracts.
     *
     * @return array<int, class-string>
     */
    private function discoverEventClasses(): array
    {
        // Anchor on the canonical app-root constant rather than a brittle relative
        // dirname() hop, so the scan can't silently miss the Events folders.
        $appRoot = defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 5);

        $files = array_merge(
            glob($appRoot.'/app/Domain/*/Events/*.php') ?: [],
            glob($appRoot.'/app/Core/*/Events/*.php') ?: [],
        );

        $classes = [];

        foreach ($files as $file) {
            $relative = str_replace([$appRoot.'/app/', '/', '.php'], ['', '\\', ''], $file);
            $class = 'Leantime\\'.$relative;

            if (! class_exists($class)) {
                continue;
            }

            $implements = class_implements($class) ?: [];
            if (in_array(LeantimeEvent::class, $implements, true)
                || in_array(LeantimeFilter::class, $implements, true)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }
}
