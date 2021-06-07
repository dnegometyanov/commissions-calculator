<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private string $output;

    /**
     * @When I run :command
     */
    public function iRun($command)
    {
        $this->output = shell_exec($command);
    }

    /**
     * @Then I see
     * @Then I see :string
     *
     * @param string $string
     *
     * @throws \Exception
     */
    public function iSee($string)
    {
        $actual   = trim($this->output);
        $expected = trim($string);

        if ($expected !== $actual) {
            throw new \Exception(sprintf('"%s" != "%s"', $actual, $expected));
        }
    }
}
