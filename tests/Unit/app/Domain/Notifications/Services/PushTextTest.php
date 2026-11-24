<?php

namespace Unit\app\Domain\Notifications\Services;

use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Notifications\Services\PushRelay;
use Leantime\Domain\Notifications\Services\PushText;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * Lock-screen wording for project notifications: the item as the title, who did what as
 * the body, instead of the email's subject and message.
 */
class PushTextTest extends TestCase
{
    private const STRINGS = [
        'notification.push_task_created' => '%s added this task',
        'notification.push_task_updated' => '%s updated this task',
        'notification.push_goal_created' => '%s added this goal',
        'notification.push_idea_updated' => '%s updated this idea',
        'notification.push_board_item_updated' => '%s updated this board item',
        'notification.push_project_updated' => '%s updated this project',
        'notification.push_commented' => '%1$s: %2$s',
        'notification.push_new_comment' => 'New comment',
        'notification.push_commented_names' => '%s commented',
        'notification.push_names_fallback' => 'New activity. Open Leantime to see it.',
        'label.someone' => 'Someone',
    ];

    private function pushText(): PushText
    {
        $language = $this->createMock(LanguageCore::class);
        $language->method('__')->willReturnCallback(fn (string $key) => self::STRINGS[$key] ?? $key);

        $users = $this->createMock(UserRepository::class);
        $users->method('getUser')->willReturnCallback(fn (int $id) => $id === 7 ? ['firstname' => 'Marcel'] : false);

        $projects = $this->createMock(ProjectRepository::class);
        $projects->method('getProject')->willReturnCallback(fn ($id) => (int) $id === 111 ? ['name' => 'Sales'] : false);

        $tickets = $this->createMock(TicketRepository::class);
        $tickets->method('getTicket')->willReturnCallback(function ($id) {
            if ((int) $id !== 2267) {
                return false;
            }
            $ticket = new TicketModel;
            $ticket->headline = 'Email campaign for <b>.org</b> users';

            return $ticket;
        });

        // Goals, ideas and board items by id, as zp_canvas_items holds them: a goal's name is its
        // title, an idea's or board item's is its description.
        return new class($language, $users, $projects, $tickets) extends PushText
        {
            protected function canvasItem(int $id): ?array
            {
                return match ($id) {
                    31 => ['title' => 'Grow revenue', 'description' => 'Hit 1M ARR by Q4', 'projectId' => 111],
                    32 => ['title' => '', 'description' => 'Referral bonus', 'projectId' => 111],
                    33 => ['title' => null, 'description' => 'Strong brand', 'projectId' => '111'],
                    // Another project's item: zp_canvas_items ids are shared by every board.
                    34 => ['title' => 'Acquire competitor', 'description' => 'Secret plan', 'projectId' => 222],
                    default => null,
                };
            }
        };
    }

    private function notification(string $module, string $action, mixed $entity, array $extra = []): NotificationModel
    {
        $notification = new NotificationModel;
        $notification->module = $module;
        $notification->action = $action;
        $notification->entity = $entity;
        $notification->projectId = 111;
        $notification->authorId = 7;
        $notification->subject = 'One of your To-Dos was updated [111] - Pricing page';
        $notification->message = "Marcel updated the To-Do: 'Pricing page'";
        foreach ($extra as $key => $value) {
            $notification->{$key} = $value;
        }

        return $notification;
    }

    public function test_a_task_update_names_the_task_and_says_who_did_it_and_where(): void
    {
        $text = $this->pushText()->forNotification($this->notification('tickets', 'updated', ['headline' => 'Pricing page']));

        $this->assertSame(['title' => 'Pricing page', 'body' => 'Marcel updated this task · Sales'], $text);
    }

    public function test_a_new_task_says_added(): void
    {
        $text = $this->pushText()->forNotification($this->notification('tickets', 'created', ['headline' => 'Pricing page']));

        $this->assertSame('Marcel added this task · Sales', $text['body']);
    }

    public function test_the_senders_own_line_wins_and_still_gets_the_project(): void
    {
        $text = $this->pushText()->forNotification($this->notification('tickets', 'status_changed', ['headline' => 'Pricing page'], [
            'pushBody' => 'Marcel moved this to Done',
        ]));

        $this->assertSame(['title' => 'Pricing page', 'body' => 'Marcel moved this to Done · Sales'], $text);
    }

    public function test_a_task_comment_is_titled_with_the_task_and_quotes_the_comment(): void
    {
        $text = $this->pushText()->forNotification($this->notification('comments', 'commented', [
            'text' => '<p>Can we ship this&nbsp;Friday?</p>',
            'module' => 'ticket',
            'moduleId' => 2267,
        ]));

        $this->assertSame(['title' => 'Email campaign for .org users', 'body' => 'Marcel: Can we ship this Friday?'], $text);
    }

    public function test_a_long_comment_is_cut_on_a_word(): void
    {
        $long = str_repeat('word ', 60);
        $text = $this->pushText()->forNotification($this->notification('comments', 'commented', [
            'text' => $long, 'module' => 'ticket', 'moduleId' => 2267,
        ]));

        $this->assertStringEndsWith('word…', $text['body']);
        $this->assertLessThanOrEqual(150, mb_strlen($text['body']));
    }

    public function test_goals_ideas_and_boards_name_the_item(): void
    {
        $pushText = $this->pushText();

        $this->assertSame(
            ['title' => 'Grow revenue', 'body' => 'Marcel added this goal · Sales'],
            $pushText->forNotification($this->notification('goalcanvas', 'created', ['title' => 'Grow revenue', 'description' => 'Hit 1M ARR by Q4']))
        );
        $this->assertSame(
            'One of your To-Dos was updated [111] - Pricing page',
            $pushText->forNotification($this->notification('goalcanvas', 'created', ['title' => '', 'description' => 'Hit 1M ARR by Q4']))['title'],
            'a goal is named by its title only; its description holds details, never a name'
        );
        $this->assertSame(
            'Marcel updated this idea · Sales',
            $pushText->forNotification($this->notification('ideas', 'updated', ['description' => 'Referral bonus']))['body']
        );
        $this->assertSame(
            'Marcel updated this board item · Sales',
            $pushText->forNotification($this->notification('swotcanvas', 'updated', ['description' => 'Strong brand']))['body']
        );
    }

    public function test_names_only_never_shows_a_goals_details(): void
    {
        $pushText = $this->pushText();

        $update = $pushText->forNotification($this->notification('goalcanvas', 'updated', ['title' => '', 'description' => 'Hit 1M ARR by Q4']), PushRelay::CONTENT_NAMES);
        $this->assertStringNotContainsString('1M ARR', $update['title'].$update['body']);

        // A comment on goal 31 is titled with the goal's title, not its description.
        $comment = $pushText->forNotification($this->notification('goalcanvas', 'commented', ['text' => 'Nice work', 'moduleId' => 31]), PushRelay::CONTENT_NAMES);
        $this->assertSame('Grow revenue', $comment['title']);
        $this->assertStringNotContainsString('1M ARR', $comment['body']);
        $this->assertStringNotContainsString('Nice work', $comment['body']);
    }

    public function test_an_item_of_another_project_is_neither_named_nor_quoted(): void
    {
        $pushText = $this->pushText();

        // Commenting on project 222's goal while the notification goes to project 111.
        $notification = $this->notification('goalcanvas', 'commented', ['text' => 'Nice work', 'moduleId' => 34]);

        $this->assertSame(
            ['title' => 'One of your To-Dos was updated [111] - Pricing page', 'body' => "Marcel updated the To-Do: 'Pricing page'"],
            $pushText->forNotification($notification),
            'falls back to the email wording, which quotes neither'
        );
        $this->assertNull($pushText->forEmail($notification));
    }

    public function test_anything_it_does_not_know_keeps_the_email_wording(): void
    {
        $notification = $this->notification('wiki', 'updated', ['title' => 'Handbook']);

        $this->assertSame(
            ['title' => 'One of your To-Dos was updated [111] - Pricing page', 'body' => "Marcel updated the To-Do: 'Pricing page'"],
            $this->pushText()->forNotification($notification)
        );
    }

    public function test_names_only_keeps_who_and_what_but_not_the_comment(): void
    {
        $text = $this->pushText()->forNotification($this->notification('comments', 'commented', [
            'text' => 'The patient result is back', 'module' => 'ticket', 'moduleId' => 2267,
        ]), PushRelay::CONTENT_NAMES);

        $this->assertSame(['title' => 'Email campaign for .org users', 'body' => 'Marcel commented · Sales'], $text);
    }

    public function test_names_only_leaves_task_lines_as_they_are(): void
    {
        $text = $this->pushText()->forNotification(
            $this->notification('tickets', 'status_changed', ['headline' => 'Pricing page'], ['pushBody' => 'Marcel moved this to Done']),
            PushRelay::CONTENT_NAMES
        );

        $this->assertSame('Marcel moved this to Done · Sales', $text['body']);
    }

    public function test_names_only_never_falls_back_to_the_email_message(): void
    {
        $text = $this->pushText()->forNotification($this->notification('wiki', 'updated', ['title' => 'Handbook']), PushRelay::CONTENT_NAMES);

        $this->assertSame('New activity. Open Leantime to see it.', $text['body']);
    }

    public function test_an_unknown_author_is_someone(): void
    {
        $text = $this->pushText()->forNotification($this->notification('tickets', 'updated', ['headline' => 'Pricing page'], ['authorId' => 99]));

        $this->assertSame('Someone updated this task · Sales', $text['body']);
    }

    public function test_the_email_digest_gets_the_same_wording_in_parts(): void
    {
        $parts = $this->pushText()->forEmail($this->notification('tickets', 'status_changed', ['id' => 12, 'headline' => 'Pricing page'], [
            'pushBody' => 'Marcel moved this to Done',
        ]));

        $this->assertSame(['group' => 'tickets:12', 'title' => 'Pricing page', 'line' => 'Marcel moved this to Done', 'quote' => ''], $parts);
    }

    public function test_a_comment_in_the_digest_sits_with_its_task_and_is_quoted(): void
    {
        $parts = $this->pushText()->forEmail($this->notification('comments', 'commented', [
            'text' => '<p>Can we ship this Friday?</p>', 'module' => 'ticket', 'moduleId' => 2267,
        ]));

        $this->assertSame(
            ['group' => 'tickets:2267', 'title' => 'Email campaign for .org users', 'line' => 'Marcel commented', 'quote' => 'Can we ship this Friday?'],
            $parts
        );
    }

    public function test_goal_idea_and_board_comments_name_their_item(): void
    {
        $pushText = $this->pushText();

        // A goal comment: the notification says goalcanvas, the comment carries the goal's id.
        $this->assertSame(
            ['title' => 'Grow revenue', 'body' => 'Marcel: Nice work'],
            $pushText->forNotification($this->notification('goalcanvas', 'commented', ['text' => 'Nice work', 'moduleId' => 31]))
        );
        // An idea comment says module 'idea' on the comment.
        $this->assertSame(
            'Referral bonus',
            $pushText->forNotification($this->notification('comments', 'commented', ['text' => 'Yes', 'module' => 'idea', 'moduleId' => 32]))['title']
        );
        // A board item comment.
        $this->assertSame(
            'Strong brand',
            $pushText->forNotification($this->notification('swotcanvas', 'commented', ['text' => 'Agreed', 'moduleId' => 33]))['title']
        );
        // A host that can't be found keeps the plain title.
        $this->assertSame(
            'New comment',
            $pushText->forNotification($this->notification('goalcanvas', 'commented', ['text' => 'x', 'moduleId' => 999]))['title']
        );
    }

    public function test_comments_in_the_digest_group_with_their_items_own_changes(): void
    {
        $pushText = $this->pushText();

        $goalComment = $pushText->forEmail($this->notification('goalcanvas', 'commented', ['text' => 'Nice work', 'moduleId' => 31]));
        $goalUpdate = $pushText->forEmail($this->notification('goalcanvas', 'updated', ['id' => 31, 'title' => 'Grow revenue']));
        $this->assertSame('goalcanvas:31', $goalComment['group']);
        $this->assertSame($goalUpdate['group'], $goalComment['group']);
        $this->assertSame('Grow revenue', $goalComment['title']);

        $ideaComment = $pushText->forEmail($this->notification('comments', 'commented', ['text' => 'Yes', 'module' => 'idea', 'moduleId' => 32]));
        $ideaUpdate = $pushText->forEmail($this->notification('ideas', 'updated', ['id' => 32, 'description' => 'Referral bonus']));
        $this->assertSame($ideaUpdate['group'], $ideaComment['group']);

        // Project comments ('project') and project updates ('projects') share one key.
        $projectComment = $pushText->forEmail($this->notification('comments', 'commented', ['text' => 'Kickoff Monday', 'module' => 'project', 'moduleId' => 111]));
        $projectUpdate = $pushText->forEmail($this->notification('projects', 'updated', ['id' => 111, 'name' => 'Sales']));
        $this->assertSame('projects:111', $projectComment['group']);
        $this->assertSame($projectUpdate['group'], $projectComment['group']);
    }

    public function test_a_comment_whose_item_cannot_be_told_keeps_the_email_wording(): void
    {
        $this->assertNull($this->pushText()->forEmail($this->notification('comments', 'commented', ['text' => 'Yes', 'moduleId' => 32])));
    }

    public function test_the_digest_keeps_the_email_wording_for_anything_unknown(): void
    {
        $this->assertNull($this->pushText()->forEmail($this->notification('wiki', 'updated', ['id' => 3, 'title' => 'Handbook'])));
    }
}
