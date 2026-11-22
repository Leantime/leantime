<?php

namespace Unit\app\Domain\Blueprints\Controllers;

use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Blueprints\Controllers\EditCanvasComment;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Goalcanvas\Controllers\EditCanvasComment as GoalEditCanvasComment;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvasRepository;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvasService;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Unit\TestCase;

/**
 * The comment dialog opens for any board item or goal the user can see, including ones outside
 * the session's project. Its notifications (and the push recipients filtered by project access)
 * must follow the item's own project: notifying the session's project sent B's comment text to
 * A's members and dropped B's.
 */
class EditCanvasCommentNotificationTest extends TestCase
{
    private const SESSION_PROJECT = 3;

    private const ITEM_PROJECT = 7;

    /** @var array<int, Notification> */
    private array $sentNotifications = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->sentNotifications = [];
        session(['currentProject' => self::SESSION_PROJECT, 'userdata.id' => 1, 'userdata.name' => 'Author']);
    }

    protected function tearDown(): void
    {
        unset($_GET['id']);

        parent::tearDown();
    }

    private function projectService(): ProjectService
    {
        $projectService = $this->createMock(ProjectService::class);
        $projectService->method('notifyProjectUsers')->willReturnCallback(function (Notification $notification): void {
            $this->sentNotifications[] = $notification;
        });

        return $projectService;
    }

    private function language(): Language
    {
        $language = $this->createMock(Language::class);
        $language->method('__')->willReturnArgument(0);

        return $language;
    }

    /**
     * The Blueprints dialog controller for a "swot" board, posting $requestParams.
     *
     * @param  array<string, mixed>  $requestParams
     */
    private function blueprintsController(array $requestParams, BlueprintsService $blueprintsService): EditCanvasComment
    {
        // IncomingRequest has its own method(), so stubs go through expects().
        $request = $this->createMock(IncomingRequest::class);
        $request->expects($this->any())->method('route')->willReturn('swot');
        $request->expects($this->any())->method('getRequestParams')->willReturn($requestParams);

        $template = $this->createMock(CanvasTemplate::class);
        $template->method('getDatabaseType')->willReturn('swotcanvas');
        $template->method('getCommentModule')->willReturn('swotcanvasitem');
        $template->method('getSessionKey')->willReturn('currentSWOTCanvas');

        $registry = $this->createMock(TemplateRegistry::class);
        $registry->method('get')->willReturn($template);

        return new EditCanvasComment(
            $request,
            $this->createMock(Template::class),
            $this->language(),
            $this->createMock(CommentRepository::class),
            $this->projectService(),
            $blueprintsService,
            $registry,
        );
    }

    /**
     * A Blueprints service whose items all live in ITEM_PROJECT (false = not visible).
     */
    private function blueprintsService(array|false $item = ['id' => 5, 'projectId' => self::ITEM_PROJECT]): BlueprintsService
    {
        $service = $this->createMock(BlueprintsService::class);
        $service->method('getCanvasItem')->willReturn($item);
        $service->method('createCanvasItem')->willReturn('5');
        $service->method('getTranslatedBoxes')->willReturn([]);

        return $service;
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function boardPosts(): array
    {
        $itemFields = [
            'changeItem' => '1',
            'description' => 'Ask @someone',
            'box' => 'swot_strengths',
            'status' => '',
            'relates' => '',
            'assumptions' => '',
            'data' => '',
            'conclusion' => '',
            'milestoneId' => '',
        ];

        return [
            'comment' => [['comment' => '1', 'text' => 'Ask @someone', 'father' => '0']],
            'item update' => [$itemFields + ['itemId' => '5']],
            'item create' => [$itemFields],
        ];
    }

    /**
     * @dataProvider boardPosts
     */
    public function test_board_notification_goes_to_the_items_project(array $requestParams): void
    {
        $this->blueprintsController($requestParams, $this->blueprintsService())->post('swot', '5');

        $this->assertCount(1, $this->sentNotifications);
        $this->assertSame(self::ITEM_PROJECT, $this->sentNotifications[0]->projectId);
    }

    public function test_board_update_without_a_resolvable_item_notifies_nobody(): void
    {
        // Never fall back to the session's project.
        $requestParams = self::boardPosts()['item update'][0];

        $this->blueprintsController($requestParams, $this->blueprintsService(false))->post('swot', '5');

        $this->assertSame([], $this->sentNotifications);
    }

    public function test_goal_comment_notification_goes_to_the_goals_project(): void
    {
        $goalService = $this->createMock(GoalcanvasService::class);
        $goalService->method('getGoalItem')->willReturn(['id' => 5, 'projectId' => self::ITEM_PROJECT]);
        app()->instance(GoalcanvasService::class, $goalService);
        app()->instance(ProjectService::class, $this->projectService());
        app()->instance(CommentRepository::class, $this->createMock(CommentRepository::class));
        // The controller resolves its board repository by this name in init().
        app()->instance('Leantime\\Domain\\goalcanvas\\Repositories\\goalcanvas', $this->createMock(GoalcanvasRepository::class));

        $_GET['id'] = '5';
        $controller = new GoalEditCanvasComment(
            $this->createMock(IncomingRequest::class),
            $this->createMock(Template::class),
            $this->language(),
        );
        $controller->post(['comment' => '1', 'text' => 'Ask @someone', 'father' => '0']);

        $this->assertCount(1, $this->sentNotifications);
        $this->assertSame(self::ITEM_PROJECT, $this->sentNotifications[0]->projectId);
    }
}
