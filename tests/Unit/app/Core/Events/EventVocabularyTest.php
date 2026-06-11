<?php

namespace Unit\app\Core\Events;

use Codeception\Test\Unit;
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
        $violations = [];

        foreach ($this->discoverEventClasses() as $class) {
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

    /**
     * Finds all classes in Domain and Core Events/ folders that implement one of the
     * class-based hook contracts.
     *
     * @return array<int, class-string>
     */
    private function discoverEventClasses(): array
    {
        $appRoot = dirname(__DIR__, 5);

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
