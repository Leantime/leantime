<?php

namespace Unit\app\Domain\Projects\Repositories;

use Illuminate\Database\MySqlConnection;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Unit\TestCase;

/**
 * Access-logic tests for the Projects repository (#3710 / #3709).
 *
 * The bug: an admin/owner with zero project memberships and no public projects
 * saw an empty sidebar, because getProjectsUserHasAccessTo() lacked the
 * admin/owner blanket-access branch that its sibling getUserProjects() already
 * had. #3710 extracts the shared rule into accessibleProjectPredicate() so the
 * two paths can't drift again.
 *
 * These tests pin the predicate's exact composition (member OR public OR
 * client-scoped OR admin/owner) and prove both callers feed it the right
 * client clause — without a live DB, by capturing the access closure each
 * query builds and running it through a recording spy. This is the
 * authorization surface Marcel flagged as the merge gate (CR #1): the admin
 * blanket must be present (case a), no extra branch may over-grant to a
 * non-admin non-member (case b), and the client-scope must use the intended
 * key (case c).
 */
class ProjectsAccessTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * A minimal query-builder stand-in that records where/orWhere-style calls
     * and returns itself so a fluent chain can run against it.
     */
    private function clauseSpy(): object
    {
        return new class
        {
            /** @var array<int, array{0: string, 1: array<int, mixed>}> */
            public array $calls = [];

            public function where(...$args): static
            {
                $this->calls[] = ['where', $args];

                return $this;
            }

            public function orWhere(...$args): static
            {
                $this->calls[] = ['orWhere', $args];

                return $this;
            }

            public function whereColumn(...$args): static
            {
                $this->calls[] = ['whereColumn', $args];

                return $this;
            }

            public function orWhereNull(...$args): static
            {
                $this->calls[] = ['orWhereNull', $args];

                return $this;
            }
        };
    }

    /**
     * A query-builder stand-in that runs every where(Closure) it receives
     * through a probe and, when it recognises the access-predicate group (the
     * one that adds the admin/owner branch), captures that closure and
     * short-circuits the rest of the query build via a sentinel exception.
     */
    private function capturingBuilder(): object
    {
        $test = $this;

        return new class($test)
        {
            public ?\Closure $accessClosure = null;

            private object $test;

            public function __construct(object $test)
            {
                $this->test = $test;
            }

            public function where($arg = null): static
            {
                if ($arg instanceof \Closure) {
                    $probe = $this->test->probeClosure($arg);
                    foreach ($probe->calls as $call) {
                        if ($call === ['orWhere', ['requestingUser.role', '>=', 40]]) {
                            $this->accessClosure = $arg;

                            // Stop the query build here — the rest is irrelevant.
                            throw new \RuntimeException('LT_ACCESS_CAPTURED');
                        }
                    }
                }

                return $this;
            }

            public function __call(string $name, array $args): static
            {
                return $this;
            }
        };
    }

    /** Run a closure through a fresh clause spy and return the spy. */
    public function probeClosure(\Closure $closure): object
    {
        $spy = $this->clauseSpy();
        $closure($spy);

        return $spy;
    }

    /**
     * Build a Projects repo whose connection hands back the capturing builder
     * and whose db helper returns a harmless wrapped column, so a real access
     * query can be built up to (and only to) the access predicate.
     */
    private function repoWithCapturingQuery(object $builder): ProjectRepository
    {
        $connection = $this->make(MySqlConnection::class, [
            'table' => fn ($table = null) => $builder,
            'raw' => fn ($value) => $value,
        ]);

        $repo = $this->make(ProjectRepository::class, []);

        $connProp = new \ReflectionProperty(ProjectRepository::class, 'connection');
        $connProp->setAccessible(true);
        $connProp->setValue($repo, $connection);

        $helperProp = new \ReflectionProperty(ProjectRepository::class, 'dbHelper');
        $helperProp->setAccessible(true);
        $helperProp->setValue($repo, $this->make(DatabaseHelper::class, [
            'wrapColumn' => fn ($column) => '`'.$column.'`',
        ]));

        return $repo;
    }

    public function test_shared_predicate_grants_member_public_admin_and_delegates_client(): void
    {
        $repo = $this->make(ProjectRepository::class, []);

        $method = new \ReflectionMethod(ProjectRepository::class, 'accessibleProjectPredicate');
        $method->setAccessible(true);

        $q = $this->clauseSpy();
        $clientClause = fn ($q2) => null;
        $method->invoke($repo, $q, 42, $clientClause);

        // Exactly four access branches — a fifth would silently widen access.
        $this->assertCount(4, $q->calls, 'The access predicate must add exactly four branches.');
        $this->assertSame(['where', ['relation.userId', 42]], $q->calls[0], 'member');
        $this->assertSame(['orWhere', ['project.psettings', 'all']], $q->calls[1], 'public');
        $this->assertSame('orWhere', $q->calls[2][0], 'client (delegated to caller clause)');
        $this->assertSame($clientClause, $q->calls[2][1][0], 'the caller-supplied client clause is forwarded unchanged');
        $this->assertSame(['orWhere', ['requestingUser.role', '>=', 40]], $q->calls[3], 'admin/owner blanket');
    }

    public function test_get_projects_user_has_access_to_scopes_client_clause_to_passed_client_id(): void
    {
        $builder = $this->capturingBuilder();
        $repo = $this->repoWithCapturingQuery($builder);

        try {
            $repo->getProjectsUserHasAccessTo(42, 'all', 7);
        } catch (\RuntimeException $e) {
            $this->assertSame('LT_ACCESS_CAPTURED', $e->getMessage());
        }

        $this->assertInstanceOf(\Closure::class, $builder->accessClosure, 'access predicate group was not built');

        $clientClause = $this->clientClauseFrom($builder->accessClosure);
        $sub = $this->clauseSpy();
        $clientClause($sub);

        $this->assertSame(['where', ['project.psettings', 'clients']], $sub->calls[0]);
        $this->assertSame(['where', ['project.clientId', 7]], $sub->calls[1], 'client-shared access must scope to the passed client id');
    }

    public function test_get_user_projects_all_scopes_client_clause_to_own_client_column(): void
    {
        $builder = $this->capturingBuilder();
        $repo = $this->repoWithCapturingQuery($builder);

        try {
            $repo->getUserProjects(42, 'all', null, 'all');
        } catch (\RuntimeException $e) {
            $this->assertSame('LT_ACCESS_CAPTURED', $e->getMessage());
        }

        $this->assertInstanceOf(\Closure::class, $builder->accessClosure, 'access predicate group was not built');

        $clientClause = $this->clientClauseFrom($builder->accessClosure);
        $sub = $this->clauseSpy();
        $clientClause($sub);

        $this->assertSame(['where', ['project.psettings', 'clients']], $sub->calls[0]);
        $this->assertSame(
            ['whereColumn', ['project.clientId', 'requestingUser.clientId']],
            $sub->calls[1],
            'getUserProjects(all) must match the requesting user\'s own client column'
        );
    }

    /** Pull the delegated client clause (the 3rd branch) out of an access closure. */
    private function clientClauseFrom(\Closure $accessClosure): \Closure
    {
        $spy = $this->probeClosure($accessClosure);
        $this->assertSame('orWhere', $spy->calls[2][0]);
        $this->assertInstanceOf(\Closure::class, $spy->calls[2][1][0]);

        return $spy->calls[2][1][0];
    }
}
