<?php


namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
use Codeception\Attribute\Depends;
use Tests\Support\Page\Acceptance\Login;

class TwoHundredResponseCest
{
    public function _before(AcceptanceTester $I, Login $loginPage)
    {
        $loginPage->login('test@leantime.io', 'test');
    }

    #[Depends('Tests\Acceptance\LoginCest:loginSuccessfully')]
    public function checkAllPagesHave200Response(AcceptanceTester $I)
    {
        $I->wantTo('Check that all pages return a 200 response code');

        foreach (glob(APP_ROOT . '/domain/*', GLOB_ONLYDIR) as $domain) {
            foreach (glob("$domain/controllers/*.php") as $method) {
                $controller_contents = file_get_contents($method);

                if (
                    ! preg_match('/public function (get|run)\(/', $controller_contents)
                    || preg_match('/public function (get|run) ?\([A-Za-z$]*\)[{\n\t ]*auth::authOrRedirect/', $controller_contents)
                ) {
                    continue;
                }

                $domain = basename($domain);
                $method = basename($method, '.php');
                $method = str_replace('class.', '', $method);

                $I->amOnPage("/{$domain}/{$method}");
                $I->seeResponseCodeIs(200);
            }
        }
    }
}
