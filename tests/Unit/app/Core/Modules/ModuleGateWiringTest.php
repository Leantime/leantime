<?php

namespace Tests\Unit\app\Core\Modules;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Modules\Contracts\StoresProjectModules;
use Leantime\Core\Modules\ProjectModules;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
use Leantime\Domain\Ideas\Services\Ideas;
use Leantime\Domain\Reports\Services\ReportEngine;
use Leantime\Domain\Search\Services\Search;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 * Every service that enforces project module switches gets them from the container.
 *
 * A missing gate would let everything through, so the services require ProjectModules rather
 * than accepting null. (Since Laravel 12 the container passes a nullable parameter's default
 * instead of resolving the class whenever that class has no binding.)
 *
 * Unit tests have no database, so the services' other dependencies (and the module store) are
 * stubbed; ProjectModules itself is left for the container to resolve.
 */
class ModuleGateWiringTest extends \Unit\TestCase
{
    /**
     * @return array<string, array{class-string}>
     */
    public static function gatingServices(): array
    {
        return [
            'permissions' => [PermissionService::class],
            'tickets' => [Tickets::class],
            'timesheets' => [Timesheets::class],
            'search' => [Search::class],
            'ideas' => [Ideas::class],
            'goals' => [Goalcanvas::class],
            'reports' => [ReportEngine::class],
        ];
    }

    /**
     * @dataProvider gatingServices
     *
     * @param  class-string  $serviceClass
     */
    public function test_the_container_injects_the_module_switches(string $serviceClass): void
    {
        foreach ((new \ReflectionMethod($serviceClass, '__construct'))->getParameters() as $parameter) {
            $dependencyClass = $parameter->getType()->getName();

            if ($dependencyClass !== ProjectModules::class) {
                app()->instance($dependencyClass, $this->createStub($dependencyClass));
            }
        }
        app()->instance(StoresProjectModules::class, $this->createStub(StoresProjectModules::class));

        $service = app()->make($serviceClass);

        $projectModules = (new \ReflectionProperty($service, 'projectModules'))->getValue($service);

        $this->assertInstanceOf(ProjectModules::class, $projectModules);
    }
}
