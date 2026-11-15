<?php

namespace Unit\app\Core\Auth;

use Leantime\Core\Auth\AccountStatus;
use Unit\TestCase;

/**
 * Only active accounts may authenticate; invited, deactivated and missing users may not.
 */
class AccountStatusTest extends TestCase
{
    public function test_active_statuses_are_active(): void
    {
        $this->assertTrue(AccountStatus::isActive(['status' => 'a']));
        $this->assertTrue(AccountStatus::isActive(['status' => 'A']));
    }

    public function test_inactive_statuses_and_missing_users_are_not_active(): void
    {
        foreach (['', '0', 'i', 'I', null] as $status) {
            $this->assertFalse(AccountStatus::isActive(['status' => $status]), 'status '.var_export($status, true));
        }

        $this->assertFalse(AccountStatus::isActive(['id' => 7]), 'a row without status');
        $this->assertFalse(AccountStatus::isActive(false), 'a user that does not exist');
        $this->assertFalse(AccountStatus::isActive(null));
    }
}
