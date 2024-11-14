<?php

declare(strict_types=1);

namespace Tests\Support;

use Facebook\WebDriver\Exception\ElementClickInterceptedException;
use Facebook\WebDriver\Exception\StaleElementReferenceException;

/**
 * Inherited Methods
 *
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 */
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    /**
     * Click on an element with retries and waits
     */
    public function clickWithRetry($selector, $timeout = 10)
    {
        $maxAttempts = 3;
        $attempt = 1;

        while ($attempt <= $maxAttempts) {
            try {
                // Wait for element to be clickable
                $this->waitForElementClickable($selector, $timeout);

                // Scroll element into view
                $this->executeJS("document.querySelector('".$selector."').scrollIntoView({behavior: 'smooth', block: 'center'});");
                $this->wait(1); // Small wait after scroll

                // Try to click
                $this->click($selector);

                return;
            } catch (ElementClickInterceptedException|StaleElementReferenceException $e) {
                if ($attempt === $maxAttempts) {
                    throw $e;
                }
                $this->wait(1);
                $attempt++;
            }
        }
    }

    /**
     * Take debug screenshot on failure
     */
    public function _failed(\Codeception\TestInterface $test, $fail)
    {
        $this->makeScreenshot('failed_'.$test->getName());
    }
}
