<?php

namespace Unit\app\Domain\Ldap\Services;

use Leantime\Domain\Ldap\Services\Ldap;
use PHPUnit\Framework\TestCase;

/**
 * ldap_get_entries() returns attribute names in lowercase. Email and group lookups used the
 * configured spelling, so an Active Directory setup with userPrincipalName / memberOf got an
 * empty email (login failed) and never matched a group (everyone got the default role).
 */
class LdapAttributeCaseTest extends TestCase
{
    private function ldap(string $groupAssignments = '{}', int $defaultRole = 20): Ldap
    {
        $ldap = (new \ReflectionClass(Ldap::class))->newInstanceWithoutConstructor();

        $this->set($ldap, 'ldapKeys', (object) [
            'username' => 'sAMAccountName',
            'groups' => 'memberOf',
            'email' => 'userPrincipalName',
            'firstname' => 'givenName',
            'lastname' => 'sn',
        ]);
        $this->set($ldap, 'ldapLtGroupAssignments', json_decode($groupAssignments));
        $this->set($ldap, 'defaultRoleKey', $defaultRole);

        return $ldap;
    }

    private function set(object $object, string $property, mixed $value): void
    {
        $reflectionProperty = new \ReflectionProperty($object, $property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }

    private function call(object $object, string $method, mixed ...$arguments): mixed
    {
        $reflectionMethod = new \ReflectionMethod($object, $method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invoke($object, ...$arguments);
    }

    /** What ldap_get_entries() hands back for one AD user: lowercase keys, "count" entries. */
    private function entry(array $groups): array
    {
        return [
            'userprincipalname' => ['count' => 1, 0 => 'ada@corp.example.com'],
            'memberof' => ['count' => count($groups)] + $groups,
            'count' => 2,
        ];
    }

    public function test_a_camel_case_email_attribute_is_found(): void
    {
        $this->assertSame(
            'ada@corp.example.com',
            $this->call($this->ldap(), 'firstAttributeValue', $this->entry([]), 'userPrincipalName')
        );
    }

    public function test_a_missing_attribute_is_an_empty_string(): void
    {
        $this->assertSame('', $this->call($this->ldap(), 'firstAttributeValue', $this->entry([]), 'mail'));
    }

    public function test_member_of_groups_map_to_the_highest_matching_role(): void
    {
        $ldap = $this->ldap('{"30":{"ltRole":"manager","ldapRole":"CN=Leantime Managers"},"40":{"ltRole":"admin","ldapRole":"CN=Leantime Admins"}}');

        $role = $this->call($ldap, 'roleFromGroups', $this->entry([
            'CN=Leantime Managers,OU=Groups,DC=corp,DC=example,DC=com',
            'CN=Leantime Admins,OU=Groups,DC=corp,DC=example,DC=com',
        ]));

        $this->assertSame(40, $role);
    }

    public function test_group_dns_match_case_insensitively(): void
    {
        $ldap = $this->ldap('{"30":{"ltRole":"manager","ldapRole":"cn=leantime managers"}}');

        $this->assertSame(30, $this->call($ldap, 'roleFromGroups', $this->entry(['CN=Leantime Managers,OU=Groups,DC=corp'])));
    }

    public function test_no_matching_group_keeps_the_default_role(): void
    {
        $ldap = $this->ldap('{"40":{"ltRole":"admin","ldapRole":"CN=Leantime Admins"}}');

        $this->assertSame(20, $this->call($ldap, 'roleFromGroups', $this->entry(['CN=Staff,OU=Groups,DC=corp'])));
        $this->assertSame(20, $this->call($ldap, 'roleFromGroups', ['count' => 0]));
    }
}
