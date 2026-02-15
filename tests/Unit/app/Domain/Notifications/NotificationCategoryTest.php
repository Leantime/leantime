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

    public function test_all_categories_have_required_structure(): void
    {
        $categories = Notification::NOTIFICATION_CATEGORIES;

        $this->assertArrayHasKey('tasks', $categories);
        $this->assertArrayHasKey('comments', $categories);
        $this->assertArrayHasKey('goals', $categories);
        $this->assertArrayHasKey('ideas', $categories);
        $this->assertArrayHasKey('projects', $categories);
        $this->assertArrayHasKey('boards', $categories);
        $this->assertCount(6, $categories);

        // Each category must have 'modules' and 'description' keys
        foreach ($categories as $key => $config) {
            $this->assertArrayHasKey('modules', $config, "Category '$key' missing 'modules' key");
            $this->assertArrayHasKey('description', $config, "Category '$key' missing 'description' key");
            $this->assertIsArray($config['modules'], "Category '$key' modules must be an array");
            $this->assertIsString($config['description'], "Category '$key' description must be a string");
            $this->assertNotEmpty($config['description'], "Category '$key' description must not be empty");
        }
    }

    public function test_goalcanvas_is_not_boards(): void
    {
        // goalcanvas specifically maps to 'goals', NOT 'boards'
        $this->assertSame('goals', Notification::getCategoryForModule('goalcanvas'));
        $this->assertNotSame('boards', Notification::getCategoryForModule('goalcanvas'));
    }

    public function test_get_category_keys_returns_all_keys(): void
    {
        $keys = Notification::getCategoryKeys();
        $this->assertCount(6, $keys);
        $this->assertContains('tasks', $keys);
        $this->assertContains('comments', $keys);
        $this->assertContains('goals', $keys);
        $this->assertContains('ideas', $keys);
        $this->assertContains('projects', $keys);
        $this->assertContains('boards', $keys);
    }

    public function test_relevance_levels_are_defined(): void
    {
        $this->assertSame('all', Notification::RELEVANCE_ALL);
        $this->assertSame('my_work', Notification::RELEVANCE_MY_WORK);
        $this->assertSame('muted', Notification::RELEVANCE_MUTED);

        $levels = Notification::RELEVANCE_LEVELS;
        $this->assertCount(3, $levels);
        $this->assertArrayHasKey('all', $levels);
        $this->assertArrayHasKey('my_work', $levels);
        $this->assertArrayHasKey('muted', $levels);
    }

    /**
     * @dataProvider relevanceLevelValidationProvider
     */
    public function test_is_valid_relevance_level(string $level, bool $expected): void
    {
        $this->assertSame($expected, Notification::isValidRelevanceLevel($level));
    }

    public static function relevanceLevelValidationProvider(): array
    {
        return [
            'all is valid' => ['all', true],
            'my_work is valid' => ['my_work', true],
            'muted is valid' => ['muted', true],
            'empty is invalid' => ['', false],
            'random string is invalid' => ['something_else', false],
            'ALL uppercase is invalid' => ['ALL', false],
        ];
    }

    public function test_notification_model_has_action_property(): void
    {
        $notification = new Notification;
        $this->assertSame('', $notification->action);

        $notification->action = 'created';
        $this->assertSame('created', $notification->action);
    }
}
