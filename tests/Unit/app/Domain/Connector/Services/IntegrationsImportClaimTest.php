<?php

namespace Unit\app\Domain\Connector\Services;

use Leantime\Domain\Connector\Repositories\Integrations as IntegrationsRepo;
use Leantime\Domain\Connector\Repositories\LeantimeEntities;
use Leantime\Domain\Connector\Services\Integrations;
use Leantime\Domain\Connector\Services\ReviewedRows;
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
     * Stores reviewed rows, as the review step does, and returns their token.
     */
    private function storeReviewedRows(): string
    {
        return (new ReviewedRows)->store([['sourceField' => 'Title', 'leantimeField' => 'headline']], [['Title' => 'One']], 'tickets');
    }

    public function test_the_first_claim_gets_the_rows_and_a_later_one_gets_nothing(): void
    {
        $token = $this->storeReviewedRows();
        $file = storage_path('app/imports/reviewed-'.$token.'.json');
        $stored = (string) file_get_contents($file);

        $first = $this->service()->claimCachedImportPayload($token);
        $this->assertSame([['Title' => 'One']], $first['values']);
        $this->assertSame('tickets', $first['entity'], 'the import uses the entity that was reviewed');
        $this->assertFileDoesNotExist($file, 'the claim removes the stored rows');

        // A parallel Confirm that read the session and the file before the first request
        // removed them still sees the same rows and token. The token was claimed: nothing.
        file_put_contents($file, $stored);
        session(['serImportToken' => $token, 'serImportEntity' => 'tickets']);
        $second = $this->service()->claimCachedImportPayload($token);
        $this->assertSame([], $second['values']);
        @unlink($file);
    }

    public function test_rows_without_a_token_are_not_imported(): void
    {
        $this->storeReviewedRows();

        $this->assertSame([], $this->service()->claimCachedImportPayload('')['values']);
    }

    public function test_a_confirm_from_an_older_review_imports_nothing(): void
    {
        // Tab A reviewed first; tab B has reviewed another file since.
        $tokenA = $this->storeReviewedRows();
        $tokenB = $this->storeReviewedRows();

        $this->assertSame([], $this->service()->claimCachedImportPayload($tokenA)['values']);
        $this->assertSame([['Title' => 'One']], $this->service()->claimCachedImportPayload($tokenB)['values'], 'tab B can still confirm its own review');
    }
}
