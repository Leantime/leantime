<?php

namespace Acceptance\API;

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Install;
use Tests\Support\Page\Acceptance\Login;

class ApiCest
{
    private string $apiKey;

    private Login $loginPage;

    private Install $installPage;

    public function _before(AcceptanceTester $I, Login $loginPage, Install $installPage)
    {
        $this->loginPage = $loginPage;
        $this->installPage = $installPage;

        // Ensure database is installed before running API tests
        $this->installPage->install(
            'test@leantime.io',
            'Test123456!',
            'John',
            'Smith',
            'Smith & Co'
        );
    }

    #[Group('api')]
    #[Depends('Acceptance\LoginCest:loginSuccessfully')]
    public function createAPIKey(AcceptanceTester $I)
    {

        $this->loginPage->login('test@leantime.io', 'test');

        // Generate API key if not exists
        $I->amOnPage('setting/editCompanySettings#/api/newApiKey');
        $I->waitForElementVisible('#firstname', 120);

        $I->fillField(['id' => 'firstname'], 'APIUser');
        $I->selectOption(['id' => 'role'], 'Administrator');
        $I->waitForElementClickable('#project_1');
        $I->wait(2);
        $I->checkOption('#project_1');
        $I->clickWithRetry('#save');

        $I->waitForElement('#apiKey');

        $this->apiKey = $I->grabValueFrom('#apiKey');

        $I->resetCookie('leantime_session', []);
        $I->deleteSessionSnapshot('leantime_session');
    }

    #[Group('api')]
    #[Depends('createAPIKey')]
    public function testJsonRpcEndpoint(AcceptanceTester $I)
    {

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('x-api-key', $this->apiKey);

        $I->sendPost('/api/jsonrpc', [
            'jsonrpc' => '2.0',
            'method' => 'leantime.rpc.Comments.pollComments',
            'params' => ['projectId' => 1],
            'id' => 1,
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'jsonrpc' => 'string',
            'result' => 'array',
            'id' => 'string',
        ]);
    }

    #[Group('api')]
    #[Depends('createAPIKey')]
    public function testInvalidJsonRpcRequest(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('x-api-key', $this->apiKey);

        $I->sendPost('/api/jsonrpc', [
            'jsonrpc' => '2.0',
            'method' => 'invalid.method',
            'params' => ['projectId' => 1],
            'id' => 1,
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType([
            'jsonrpc' => 'string',
            'error' => [
                'code' => 'integer',
                'message' => 'string',
                'data' => 'string',
            ],
            'id' => 'integer',
        ]);

    }

    #[Group('api')]
    #[Depends('createAPIKey')]
    public function testValidReturnId(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('x-api-key', $this->apiKey);

        $I->sendPost('/api/jsonrpc', [
            'jsonrpc' => '2.0',
            'method' => 'leantime.rpc.Comments.pollComments',
            'params' => ['projectId' => 1],
            'id' => 123,
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson([
            'jsonrpc' => '2.0',
            'method' => 'leantime.rpc.Comments.pollComments',
            'params' => ['projectId' => 1],
            'id' => 'integer',
        ]);

    }

    #[Group('api')]
    #[Depends('createAPIKey')]
    public function testMissingApiKey(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPost('/api/jsonrpc', [
            'jsonrpc' => '2.0',
            'method' => 'leantime.rpc.Comments.pollComments',
            'params' => ['projectId' => 1],
            'id' => 1,
        ]);

        $I->seeResponseCodeIs(401);
    }
}
