<?php

namespace Unit\app\Domain\Help\Services;

use Leantime\Domain\Help\Services\Helper;
use Leantime\Domain\Setting\Repositories\Setting;
use Unit\TestCase;

/**
 * Unit tests for the onboarding / modal orchestration extracted from the
 * Help FirstLogin and ShowOnboardingDialog controllers into the Helper service.
 */
class HelperTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a Helper service with a stubbed Setting repository.
     */
    private function makeService(): Helper
    {
        return new Helper($this->make(Setting::class));
    }

    public function test_resolve_first_login_step_returns_end_step(): void
    {
        $step = $this->makeService()->resolveFirstLoginStep('end');

        $this->assertTrue($step['isEnd']);
        $this->assertSame('end', $step['key']);
        $this->assertNull($step['next']);
        $this->assertSame('help.firstLoginEnd', $step['template']);
    }

    public function test_handle_first_login_step_rejects_missing_step(): void
    {
        $result = $this->makeService()->handleFirstLoginStep([]);

        $this->assertFalse($result['valid']);
        $this->assertSame('', $result['next']);
    }

    public function test_handle_first_login_step_rejects_non_numeric_step(): void
    {
        $result = $this->makeService()->handleFirstLoginStep(['currentStep' => 'foo']);

        $this->assertFalse($result['valid']);
        $this->assertSame('', $result['next']);
    }

    public function test_handle_first_login_step_rejects_unknown_numeric_step(): void
    {
        $result = $this->makeService()->handleFirstLoginStep(['currentStep' => '999']);

        $this->assertFalse($result['valid']);
        $this->assertSame('', $result['next']);
    }

    public function test_get_helper_modal_by_route_returns_notfound_for_unknown_route(): void
    {
        $modal = $this->makeService()->getHelperModalByRoute('does.notExist');

        $this->assertSame('notfound', $modal['template']);
    }

    public function test_mark_modal_seen_for_module_sanitizes_and_records_session(): void
    {
        session()->forget('usersettings.modals');

        $template = $this->makeService()->markModalSeenForModule('<b>dashboard</b>');

        $expected = htmlspecialchars('<b>dashboard</b>');
        $this->assertSame($expected, $template);
        $this->assertSame(1, session('usersettings.modals.'.$expected));
    }

    public function test_mark_modal_seen_for_route_resolves_template_and_records_session(): void
    {
        session()->forget('usersettings.modals');

        $template = $this->makeService()->markModalSeenForRoute('dashboard.show');

        $this->assertSame('projectDashboard', $template);
        $this->assertSame(1, session('usersettings.modals.projectDashboard'));
    }

    public function test_mark_modal_seen_for_route_unknown_route_marks_notfound(): void
    {
        session()->forget('usersettings.modals');

        $template = $this->makeService()->markModalSeenForRoute('does.notExist');

        $this->assertSame('notfound', $template);
        $this->assertSame(1, session('usersettings.modals.notfound'));
    }
}
