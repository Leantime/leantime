<?php

namespace Unit\app\Domain\Ldap\Services;

use Leantime\Domain\Ldap\Services\Ldap;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * The LDAP import updates users that already exist, whatever their status.
 *
 * It only looked for active accounts, so a user an admin deactivated looked new: the import
 * inserted it again, hit the unique username and stopped with a database error.
 */
class LdapUpsertUsersTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_a_deactivated_user_is_updated_and_not_inserted_again(): void
    {
        $writes = [];
        $deactivatedRow = ['id' => 7, 'username' => 'ada@example.com', 'status' => ''];

        app()->instance(UserRepository::class, $this->make(UserRepository::class, [
            'getUserByEmail' => fn (string $email, string $status = 'a') => $status === 'a' ? false : $deactivatedRow,
            'patchUser' => function (int $id, array $params) use (&$writes) {
                $writes[] = ['patchUser', $id, $params];

                return true;
            },
            'addUser' => function () use (&$writes) {
                $writes[] = ['addUser'];

                return '99';
            },
        ]));

        $ldap = (new \ReflectionClass(Ldap::class))->newInstanceWithoutConstructor();

        $this->assertTrue($ldap->upsertUsers([[
            'firstname' => 'Ada',
            'lastname' => 'Lovelace',
            'phone' => '',
            'user' => 'ada@example.com',
            'role' => 20,
            'jobTitle' => '',
            'jobLevel' => '',
            'department' => '',
        ]]));

        // patchUser only carries the name and role: the account stays deactivated.
        $this->assertSame([['patchUser', 7, ['firstname' => 'Ada', 'lastname' => 'Lovelace', 'role' => 20]]], $writes);
    }
}
