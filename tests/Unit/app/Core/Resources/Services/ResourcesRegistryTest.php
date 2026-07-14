<?php

declare(strict_types=1);

namespace Unit\app\Core\Resources\Services;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Resources\Contracts\ResourcesGateway;
use Leantime\Core\Resources\Models\ResourceSummary;
use Leantime\Core\Resources\Services\ResourcesRegistry;
use Unit\TestCase;

/**
 * Unit tests for ResourcesRegistry — the one-plugin-owns-it contract.
 *
 * Behaviors under test:
 *   - null on read when no provider is registered (honest "not installed")
 *   - a registered provider is returned by resolve()
 *   - re-registering the SAME provider class is idempotent
 *   - a DIFFERENT provider class trying to register is refused (first wins)
 */
class ResourcesRegistryTest extends TestCase
{
    public function test_resolve_returns_null_when_no_provider_registered(): void
    {
        $registry = new ResourcesRegistry;

        $this->assertNull($registry->resolve());
        $this->assertFalse($registry->hasProvider());
    }

    public function test_resolve_returns_registered_gateway(): void
    {
        $registry = new ResourcesRegistry;
        $gateway = $this->makeGateway();

        $registry->register($gateway);

        $this->assertSame($gateway, $registry->resolve());
        $this->assertTrue($registry->hasProvider());
    }

    public function test_reregistering_same_provider_class_is_idempotent(): void
    {
        $registry = new ResourcesRegistry;
        $first = $this->makeGateway();
        $second = $this->makeGateway(); // same anonymous class

        $registry->register($first);
        $registry->register($second);

        // Second registration replaces first because it's the same class.
        // (No warning is emitted — this is the "plugin re-registered on
        // hot-reload" case, not a conflict.)
        $this->assertSame($second, $registry->resolve());
    }

    public function test_different_provider_class_registration_is_refused(): void
    {
        Log::spy();

        $registry = new ResourcesRegistry;
        $first = $this->makeGateway();
        $second = $this->makeOtherGateway();

        $registry->register($first);
        $registry->register($second);

        $this->assertSame(
            $first,
            $registry->resolve(),
            'First registration must win when a different class tries to register',
        );

        // Logging the refused registration is part of the contract — a silent
        // refusal would let double-installs go unnoticed.
        Log::shouldHaveReceived('warning')->once()->withArgs(
            fn (string $message): bool => str_contains($message, 'ResourcesRegistry')
                && str_contains($message, 'already registered')
        );
    }

    private function makeGateway(): ResourcesGateway
    {
        return new class implements ResourcesGateway
        {
            public function getForProjects(array $projectIds): ResourceSummary
            {
                return ResourceSummary::empty($projectIds);
            }

            public function getForProgram(int $programId): ResourceSummary
            {
                return ResourceSummary::empty([$programId]);
            }
        };
    }

    private function makeOtherGateway(): ResourcesGateway
    {
        return new class implements ResourcesGateway
        {
            public function getForProjects(array $projectIds): ResourceSummary
            {
                return ResourceSummary::empty($projectIds);
            }

            public function getForProgram(int $programId): ResourceSummary
            {
                return ResourceSummary::empty([$programId]);
            }
        };
    }
}
