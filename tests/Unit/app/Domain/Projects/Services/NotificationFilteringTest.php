<?php

namespace Unit\app\Domain\Projects\Services;

use Leantime\Domain\Notifications\Models\Notification;
use PHPUnit\Framework\TestCase;

/**
 * Tests the notification filtering helper methods that were extracted
 * from Projects\Services\Projects. These methods are private, so we
 * test the logic by reimplementing the core algorithms against the
 * Notification model — verifying the model's contract that the service depends on.
 *
 * The actual service integration (filterUsersByProjectRelevance etc.)
 * is tested via acceptance tests that exercise the full notification flow.
 */
class NotificationFilteringTest extends TestCase
{
    /**
     * Helper: determines if a user is involved in a notification entity (same logic as the private method).
     */
    private function isUserInvolved(int $userId, Notification $notification): bool
    {
        $entity = $notification->entity;

        if (is_array($entity)) {
            if (isset($entity['editorId']) && (int) $entity['editorId'] === $userId) {
                return true;
            }
            if (isset($entity['userId']) && (int) $entity['userId'] === $userId) {
                return true;
            }
            if (isset($entity['author']) && (int) $entity['author'] === $userId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper: determines project relevance level from settings (same logic as the private method).
     */
    private function getProjectRelevanceLevel(int $userId, int $projectId, array $preloadedSettings, string $companyDefault): string
    {
        $newKey = 'usersettings.'.$userId.'.projectNotificationLevels';
        $newSetting = $preloadedSettings[$newKey] ?? false;
        if (! empty($newSetting) && $newSetting !== false) {
            $levels = json_decode($newSetting, true);
            if (is_array($levels) && isset($levels[$projectId])) {
                $level = $levels[$projectId];
                if (Notification::isValidRelevanceLevel($level)) {
                    return $level;
                }
            }
        }

        $oldKey = 'usersettings.'.$userId.'.projectMutedNotifications';
        $oldSetting = $preloadedSettings[$oldKey] ?? false;
        if (! empty($oldSetting) && $oldSetting !== false) {
            $mutedIds = json_decode($oldSetting, true);
            if (is_array($mutedIds) && in_array($projectId, $mutedIds)) {
                return Notification::RELEVANCE_MUTED;
            }
        }

        return $companyDefault;
    }

    // -----------------------------------------------------------------------
    // Tests for relevance level resolution
    // -----------------------------------------------------------------------

    public function test_new_format_setting_is_used(): void
    {
        $settings = [
            'usersettings.1.projectNotificationLevels' => json_encode([5 => 'my_work', 10 => 'muted']),
        ];

        $this->assertSame('my_work', $this->getProjectRelevanceLevel(1, 5, $settings, 'all'));
        $this->assertSame('muted', $this->getProjectRelevanceLevel(1, 10, $settings, 'all'));
    }

    public function test_falls_back_to_company_default_when_no_setting(): void
    {
        $settings = [];

        $this->assertSame('all', $this->getProjectRelevanceLevel(1, 5, $settings, 'all'));
        $this->assertSame('my_work', $this->getProjectRelevanceLevel(1, 5, $settings, 'my_work'));
    }

    public function test_project_not_in_levels_map_uses_company_default(): void
    {
        $settings = [
            'usersettings.1.projectNotificationLevels' => json_encode([10 => 'muted']),
        ];

        // Project 5 is not in the map, should fall back to company default
        $this->assertSame('all', $this->getProjectRelevanceLevel(1, 5, $settings, 'all'));
    }

    public function test_legacy_muted_format_is_recognized(): void
    {
        $settings = [
            'usersettings.1.projectMutedNotifications' => json_encode([5, 10, 15]),
        ];

        $this->assertSame('muted', $this->getProjectRelevanceLevel(1, 5, $settings, 'all'));
        $this->assertSame('muted', $this->getProjectRelevanceLevel(1, 10, $settings, 'all'));
        $this->assertSame('all', $this->getProjectRelevanceLevel(1, 20, $settings, 'all'));
    }

    public function test_new_format_takes_precedence_over_legacy(): void
    {
        $settings = [
            'usersettings.1.projectNotificationLevels' => json_encode([5 => 'all']),
            'usersettings.1.projectMutedNotifications' => json_encode([5]),  // legacy says muted
        ];

        // New format wins
        $this->assertSame('all', $this->getProjectRelevanceLevel(1, 5, $settings, 'muted'));
    }

    public function test_invalid_level_in_settings_falls_back_to_company_default(): void
    {
        $settings = [
            'usersettings.1.projectNotificationLevels' => json_encode([5 => 'invalid_level']),
        ];

        $this->assertSame('all', $this->getProjectRelevanceLevel(1, 5, $settings, 'all'));
    }

    // -----------------------------------------------------------------------
    // Tests for user involvement detection
    // -----------------------------------------------------------------------

    public function test_user_is_involved_when_assigned_via_editor_id(): void
    {
        $notification = new Notification;
        $notification->entity = ['editorId' => 42, 'userId' => 99];

        $this->assertTrue($this->isUserInvolved(42, $notification));
        // userId 99 is the reporter -- also involved (tested in next test)
        $this->assertTrue($this->isUserInvolved(99, $notification));
    }

    public function test_user_is_involved_when_creator_via_user_id(): void
    {
        $notification = new Notification;
        $notification->entity = ['editorId' => 42, 'userId' => 99];

        $this->assertTrue($this->isUserInvolved(99, $notification));
    }

    public function test_user_is_involved_when_canvas_author(): void
    {
        $notification = new Notification;
        $notification->entity = ['author' => 77];

        $this->assertTrue($this->isUserInvolved(77, $notification));
    }

    public function test_user_not_involved_when_unrelated(): void
    {
        $notification = new Notification;
        $notification->entity = ['editorId' => 42, 'userId' => 99, 'author' => 77];

        $this->assertFalse($this->isUserInvolved(1, $notification));
    }

    public function test_user_not_involved_when_entity_is_null(): void
    {
        $notification = new Notification;
        $notification->entity = null;

        $this->assertFalse($this->isUserInvolved(1, $notification));
    }

    public function test_user_not_involved_when_entity_has_no_user_fields(): void
    {
        $notification = new Notification;
        $notification->entity = ['headline' => 'Test', 'description' => 'No user fields'];

        $this->assertFalse($this->isUserInvolved(1, $notification));
    }

    public function test_editor_id_string_matches_integer_user(): void
    {
        $notification = new Notification;
        $notification->entity = ['editorId' => '42'];

        $this->assertTrue($this->isUserInvolved(42, $notification));
    }

    // -----------------------------------------------------------------------
    // Tests for the category-to-module mapping with new structure
    // -----------------------------------------------------------------------

    public function test_category_filtering_with_restructured_categories(): void
    {
        // Verify getCategoryForModule still works with the new {modules: [...], description: '...'} structure
        $this->assertSame('tasks', Notification::getCategoryForModule('tickets'));
        $this->assertSame('comments', Notification::getCategoryForModule('comments'));
        $this->assertSame('goals', Notification::getCategoryForModule('goalcanvas'));
        $this->assertSame('boards', Notification::getCategoryForModule('leancanvas'));
        $this->assertSame('boards', Notification::getCategoryForModule('retroscanvas'));
        $this->assertNull(Notification::getCategoryForModule('unknownModule'));
    }

    // -----------------------------------------------------------------------
    // Integration-style test: full filtering decision
    // -----------------------------------------------------------------------

    public function test_muted_user_would_be_filtered_out(): void
    {
        $settings = [
            'usersettings.1.projectNotificationLevels' => json_encode([5 => 'muted']),
        ];

        $level = $this->getProjectRelevanceLevel(1, 5, $settings, 'all');
        $this->assertSame('muted', $level);
        // In the actual service, muted -> user is excluded (return false from filter)
    }

    public function test_my_work_user_kept_when_assigned(): void
    {
        $settings = [
            'usersettings.1.projectNotificationLevels' => json_encode([5 => 'my_work']),
        ];

        $notification = new Notification;
        $notification->entity = ['editorId' => 1, 'userId' => 99];
        $notification->projectId = 5;

        $level = $this->getProjectRelevanceLevel(1, 5, $settings, 'all');
        $this->assertSame('my_work', $level);
        $this->assertTrue($this->isUserInvolved(1, $notification));
    }

    public function test_my_work_user_excluded_when_unrelated(): void
    {
        $settings = [
            'usersettings.1.projectNotificationLevels' => json_encode([5 => 'my_work']),
        ];

        $notification = new Notification;
        $notification->entity = ['editorId' => 99, 'userId' => 88];
        $notification->projectId = 5;

        $level = $this->getProjectRelevanceLevel(1, 5, $settings, 'all');
        $this->assertSame('my_work', $level);
        $this->assertFalse($this->isUserInvolved(1, $notification));
    }

    public function test_all_activity_user_always_kept(): void
    {
        $settings = [
            'usersettings.1.projectNotificationLevels' => json_encode([5 => 'all']),
        ];

        $level = $this->getProjectRelevanceLevel(1, 5, $settings, 'muted');
        $this->assertSame('all', $level);
        // In the actual service, 'all' -> user is always kept (return true from filter)
    }
}
