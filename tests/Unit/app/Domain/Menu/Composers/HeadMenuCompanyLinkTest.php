<?php

namespace Unit\app\Domain\Menu\Composers;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Menu\Composers\HeadMenu;
use Leantime\Domain\Menu\Events\CompanyPagesFilter;

/**
 * The header's Company link opens the first company page the user may open; plugins add their
 * company pages through CompanyPagesFilter.
 */
class HeadMenuCompanyLinkTest extends \Unit\TestCase
{
    private const DISPATCHER_STATE = ['filterRegistry', 'filterRegistryVersion', 'patternMatchCache', 'compiledPatternCache', 'available_hooks'];

    private array $dispatcherSnapshot = [];

    protected function setUp(): void
    {
        parent::setUp();

        $dispatcher = new \ReflectionClass(EventDispatcher::class);
        foreach (self::DISPATCHER_STATE as $property) {
            $this->dispatcherSnapshot[$property] = $dispatcher->getProperty($property)->getValue();
        }
    }

    protected function tearDown(): void
    {
        $dispatcher = new \ReflectionClass(EventDispatcher::class);
        foreach ($this->dispatcherSnapshot as $property => $value) {
            $dispatcher->getProperty($property)->setValue(null, $value);
        }

        parent::tearDown();
    }

    public function test_the_link_opens_the_first_company_page_the_user_may_open(): void
    {
        $this->assertSame('/users/showAll/', $this->companyLinkFor(['users.view', 'clients.view']));
    }

    public function test_no_company_permission_means_no_link(): void
    {
        $this->assertNull($this->companyLinkFor(['roles.manage']));
    }

    public function test_a_plugin_page_gives_its_permission_holders_the_link(): void
    {
        EventDispatcher::add_filter_listener(
            CompanyPagesFilter::class,
            fn (array $pages) => $pages + ['roles.manage' => '/customRoles/roles/'],
        );

        $this->assertSame('/customRoles/roles/', $this->companyLinkFor(['roles.manage']));
        $this->assertSame('/users/showAll/', $this->companyLinkFor(['roles.manage', 'users.view']));
    }

    /**
     * @param  array<int, string>  $grantedPermissions
     */
    private function companyLinkFor(array $grantedPermissions): ?string
    {
        $permissions = $this->createMock(PermissionService::class);
        $permissions->method('currentUserCan')->willReturnCallback(
            fn (string $permission) => in_array($permission, $grantedPermissions, true)
        );

        $headMenu = (new \ReflectionClass(HeadMenu::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(HeadMenu::class, 'permissionService'))->setValue($headMenu, $permissions);

        return (new \ReflectionMethod(HeadMenu::class, 'companyLink'))->invoke($headMenu);
    }
}
