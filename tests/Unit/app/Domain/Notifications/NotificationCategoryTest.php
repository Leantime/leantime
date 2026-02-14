<?php

namespace Unit\app\Domain\Notifications;

use Leantime\Domain\Notifications\Models\Notification;
use PHPUnit\Framework\TestCase;

class NotificationCategoryTest extends TestCase
{
    /**
     * @dataProvider moduleToCategoryProvider
     */
    public function test_get_category_for_module_return_correct_category(string $module, ?string $expectedCategory): void
    {
        $this->assertSame($expectedCategory, Notification::getCategoryForModule($module));
    }

    public static function moduleToCategoryProvider(): array
    {
        return [
            'tickets maps to tasks' => ['tickets', 'tasks'],
            'comments maps to comments' => ['comments', 'comments'],
            'goalcanvas maps to goals' => ['goalcanvas', 'goals'],
            'ideas maps to ideas' => ['ideas', 'ideas'],
            'projects maps to projects' => ['projects', 'projects'],
            'leancanvas maps to boards' => ['leancanvas', 'boards'],
            'swotcanvas maps to boards' => ['swotcanvas', 'boards'],
            'retroscanvas maps to boards' => ['retroscanvas', 'boards'],
            'cpcanvas maps to boards' => ['cpcanvas', 'boards'],
            'unknown module returns null' => ['someOtherModule', null],
        ];
    }

    public function test_all_categories_have_modules_or_are_catch_all(): void
    {
        $categories = Notification::NOTIFICATION_CATEGORIES;

        $this->assertArrayHasKey('tasks', $categories);
        $this->assertArrayHasKey('comments', $categories);
        $this->assertArrayHasKey('goals', $categories);
        $this->assertArrayHasKey('ideas', $categories);
        $this->assertArrayHasKey('projects', $categories);
        $this->assertArrayHasKey('boards', $categories);
        $this->assertCount(6, $categories);
    }

    public function test_goalcanvas_is_not_boards(): void
    {
        // goalcanvas specifically maps to 'goals', NOT 'boards'
        $this->assertSame('goals', Notification::getCategoryForModule('goalcanvas'));
        $this->assertNotSame('boards', Notification::getCategoryForModule('goalcanvas'));
    }
}
