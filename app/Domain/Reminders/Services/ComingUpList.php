<?php

namespace Leantime\Domain\Reminders\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Language as LanguageCore;

/**
 * The "Coming up" panel's content, from the same summary the morning email is built from (with
 * every item, not the top 5): sections Today / Tomorrow / Later this week / Later (the email's
 * grouping), the routine line under Today, and the quiet "still open from earlier" line.
 *
 * Each row gets a plain "why" line: when it's due or ends ("today", "tomorrow", the weekday
 * within a week, else "in N days", in the person's timezone), then its project, or "You asked
 * to be reminded" for someone else's to-do the person opted in to.
 */
class ComingUpList
{
    public function __construct(private LanguageCore $language) {}

    /**
     * @param  array<string, mixed>  $summary  From {@see SummaryBuilder::build()} with limit 0.
     * @return array{
     *     sections: array<int, array{key: string, label: string, items: array<int, array<string, mixed>>, note: string|null}>,
     *     stillOpen: string|null,
     *     isEmpty: bool
     * }
     */
    public function present(array $summary, string $timezone): array
    {
        $sections = [
            'today' => ['key' => 'today', 'label' => $this->language->__('reminders.panel_today'), 'items' => [], 'note' => null],
            'tomorrow' => ['key' => 'tomorrow', 'label' => $this->language->__('reminders.panel_tomorrow'), 'items' => [], 'note' => null],
            'week' => ['key' => 'week', 'label' => $this->language->__('reminders.panel_this_week'), 'items' => [], 'note' => null],
            'later' => ['key' => 'later', 'label' => $this->language->__('reminders.panel_later'), 'items' => [], 'note' => null],
        ];

        foreach ($summary['items'] as $item) {
            $section = match (true) {
                $item['daysUntil'] <= 0 => 'today',
                $item['daysUntil'] === 1 => 'tomorrow',
                $item['daysUntil'] <= 7 => 'week',
                default => 'later',
            };
            $sections[$section]['items'][] = $this->row($item, $timezone);
        }

        if (($summary['routine']['count'] ?? 0) > 0) {
            $sections['today']['note'] = sprintf($this->language->__('reminders.routine'), $summary['routine']['count']);
        }

        $sections = array_values(array_filter($sections, fn (array $section) => $section['items'] !== [] || $section['note'] !== null));

        return [
            'sections' => $sections,
            'stillOpen' => ($summary['overdueCount'] ?? 0) > 0 ? sprintf($this->language->__('reminders.still_open'), $summary['overdueCount']) : null,
            'isEmpty' => $sections === [],
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function row(array $item, string $timezone): array
    {
        $when = $this->when((int) $item['daysUntil'], (string) $item['due'], $timezone);
        $what = sprintf($this->language->__('reminders.why_'.$item['kind']), $when);

        $context = match (true) {
            ($item['isYours'] ?? true) === false => $this->language->__('reminders.panel_asked'),
            $item['kind'] !== 'project' && $item['projectName'] !== '' => $item['projectName'],
            default => null,
        };

        return [
            'kind' => $item['kind'],
            'id' => $item['id'],
            'title' => $item['title'],
            'href' => self::inPageLink((string) $item['url']),
            'why' => $context === null ? $what : $what.' · '.$context,
            'isYours' => $item['isYours'] ?? true,
        ];
    }

    /** "today", "tomorrow", "Friday" within the week, otherwise "in 12 days". */
    private function when(int $daysUntil, string $dueUtc, string $timezone): string
    {
        return match (true) {
            $daysUntil <= 0 => $this->language->__('reminders.when_today'),
            $daysUntil === 1 => $this->language->__('reminders.when_tomorrow'),
            $daysUntil < 7 && $dueUtc !== '' => CarbonImmutable::parse($dueUtc)->setTimezone($timezone)->format('l'),
            default => sprintf($this->language->__('reminders.when_in_days'), $daysUntil),
        };
    }

    /**
     * Task and milestone links open their dialog on the page the panel is on (just the "#/…"
     * part); a project link leaves the page.
     */
    public static function inPageLink(string $url): string
    {
        $hash = strpos($url, '#/');

        return $hash === false ? $url : substr($url, $hash);
    }
}
