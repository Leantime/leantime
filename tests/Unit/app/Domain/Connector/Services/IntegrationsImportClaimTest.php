<?php

namespace Unit\app\Domain\Connector\Services;

use Leantime\Domain\Connector\Repositories\Integrations as IntegrationsRepo;
use Leantime\Domain\Connector\Repositories\LeantimeEntities;
use Leantime\Domain\Connector\Services\Integrations;
use Unit\TestCase;

/**
 * The reviewed import payload can be claimed by one request only.
 */
class IntegrationsImportClaimTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function service(): Integrations
    {
        return new Integrations($this->make(IntegrationsRepo::class), $this->make(LeantimeEntities::class));
    }

    /**
     * Puts a reviewed payload in the session, as the review step does.
     */
    private function storeReviewedRows(string $token): void
    {
        session([
            'serValues' => serialize([['Title' => 'One']]),
            'serFields' => serialize([['sourceField' => 'Title', 'leantimeField' => 'headline']]),
            'serImportEntity' => 'tickets',
            'serImportToken' => $token,
        ]);
    }

    public function test_the_first_claim_gets_the_rows_and_a_later_one_gets_nothing(): void
    {
        $token = 'claim-test-'.uniqid();
        $this->storeReviewedRows($token);

        $first = $this->service()->claimCachedImportPayload($token);
        $this->assertSame([['Title' => 'One']], $first['values']);
        $this->assertSame('tickets', $first['entity'], 'the import uses the entity that was reviewed');
        $this->assertNull(session('serValues'), 'the claim clears the session');

        // A second Confirm that read the session before the first request saved its changes
        // still sees the same rows and token. The token was claimed, so it gets nothing.
        $this->storeReviewedRows($token);
        $second = $this->service()->claimCachedImportPayload($token);
        $this->assertSame([], $second['values']);
    }

    public function test_rows_without_a_token_are_not_imported(): void
    {
        session(['serValues' => serialize([['Title' => 'One']]), 'serFields' => serialize([['sourceField' => 'Title', 'leantimeField' => 'headline']])]);
        session()->forget('serImportToken');

        $this->assertSame([], $this->service()->claimCachedImportPayload('')['values']);
    }

    public function test_a_confirm_from_an_older_review_imports_nothing(): void
    {
        // Tab A reviewed with token "a"; tab B has reviewed another file since.
        $this->storeReviewedRows('claim-test-b-'.uniqid());

        $this->assertSame([], $this->service()->claimCachedImportPayload('claim-test-a')['values']);
        $this->assertNotNull(session('serValues'), 'tab B can still confirm its own review');
    }
}
