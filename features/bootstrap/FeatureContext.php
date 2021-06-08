<?php

use Behat\Behat\Context\Context;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private string $output;

    /**
     * @When I run :command
     *
     * @param string $command
     */
    public function iRun(string $command): void
    {
        $this->output = shell_exec($command);
    }

    /**
     * @When I run in test env with mocked exchange rates data :command
     *
     * @param string $command
     */
    public function iRunTestEnvMocked(string $command): void
    {
        $this->output = shell_exec('APP_ENV=test ' . $command);
    }

    /**
     * @Then I see
     * @Then I see :string
     *
     * @param string $string
     *
     * @throws \Exception
     */
    public function iSee(string $string): void
    {
        $actual   = trim($this->output);
        $expected = trim($string);

        if ($expected !== $actual) {
            throw new \Exception(sprintf('"%s" != "%s"', $actual, $expected));
        }
    }

    /**
     * @Then I see :expectedLinesCount lines of result
     *
     * @param int $expectedLinesCount
     *
     * @throws \Exception
     */
    public function iSeeCount(int $expectedLinesCount): void
    {
        $actual   = trim($this->output);
        $actualLinesCount = count(explode(PHP_EOL, $actual));

        if ($expectedLinesCount !== $actualLinesCount) {
            throw new \Exception(sprintf('Lines count does not match: "%s" != "%s"', $actualLinesCount, $expectedLinesCount));
        }
    }
}
