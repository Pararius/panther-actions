<?php

declare(strict_types=1);

namespace PantherActions\Behat;

use Behat\Behat\Context\Context;
use PantherActions\PantherActions;
use Symfony\Component\Panther\PantherTestCase;

class PantherContext extends PantherTestCase implements Context
{
    use PantherActions;

    public const CHROME = 'chrome';
    public const FIREFOX = 'firefox';

    /**
     * @BeforeSuite
     */
    public static function beforeSuite(): void
    {
        self::createPantherClient(['browser' => self::CHROME]);
    }

    /**
     * @AfterSuite
     */
    public static function afterSuite(): void
    {
        if (self::$pantherClient !== null) {
            self::$pantherClient->quit();
        }
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario(): void
    {
        self::$pantherClient?->getCookieJar()->clear();
    }

    /**
     * @Given /^(?:|I )am on (?:|the )homepage$/
     * @When /^(?:|I )go to (?:|the )homepage$/
     */
    public function i_am_on_homepage(): void
    {
        self::goToHomepage();
    }

    /**
     * @Given /^(?:|I )am on "(?P<page>[^"]+)"$/
     * @When /^(?:|I )go to "(?P<page>[^"]+)"$/
     */
    public function i_go_to(string $page): void
    {
        self::goTo($page);
    }

    /**
     * @Then I should be on :page
     */
    public function i_should_be_on_address(string $page): void
    {
        self::assertCurrentAddressMatches($page);
    }

    /**
     * @Then /^the (?i)url(?-i) should match (?P<pattern>"(?:[^"]|\\")*")$/
     */
    public function i_should_be_on_address_matching(string $pattern): void
    {
        $pattern = trim(self::fixStepArgument($pattern), '"');
        self::assertCurrentAddressMatchesPattern($pattern);
    }

    /**
     * @Then the url should contain :path
     */
    public function i_should_be_on_address_containing(string $path): void
    {
        self::assertCurrentAddressContains($path);
    }

    /**
     * @Then /^(?:|I )should be on (?:|the )homepage$/
     */
    public function i_should_be_on_homepage(): void
    {
        self::assertOnHomepage();
    }

    /**
     * @When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)"$/
     */
    public function i_follow_link(string $linkText): void
    {
        $linkText = self::fixStepArgument($linkText);

        self::followLink($linkText);
    }

    /**
     * @When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)"$/
     */
    public function i_press_button(string $button): void
    {
        $button = self::fixStepArgument($button);

        self::pressButton($button);
    }

    /**
     * @Then I should see the button :button
     */
    public function i_should_see_the_button(string $button): void
    {
        $button = self::fixStepArgument($button);

        self::assertButtonVisible($button);
    }

    /**
     * @Then I should not see the button :button
     */
    public function i_should_not_see_the_button(string $button): void
    {
        $button = self::fixStepArgument($button);

        self::assertButtonNotVisible($button);
    }

    /**
     * @Then I should see the link :link
     */
    public function i_should_see_the_link(string $link): void
    {
        $link = self::fixStepArgument($link);

        self::assertLinkVisible($link);
    }

    /**
     * @Then I should not see the link :link
     */
    public function i_should_not_see_the_link(string $link): void
    {
        $link = self::fixStepArgument($link);

        self::assertLinkNotVisible($link);
    }

    /**
     * @Then the page title should contain :title
     */
    public function the_page_title_should_contain(string $title): void
    {
        $title = self::fixStepArgument($title);
        self::assertPageTitleContainsIgnoringCase($title);
    }

    /**
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function i_should_see_text(string $text): void
    {
        $text = self::fixStepArgument($text);

        self::assertPageContainsText($text);
    }

    /**
     * @Then /^(?:|I )should not see "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function i_should_not_see_text(string $text): void
    {
        $text = self::fixStepArgument($text);

        self::assertPageDoesNotContainText($text);
    }

    /**
     * @Then /^(?:|I )should see text matching (?P<pattern>"(?:[^"]|\\")*")$/
     */
    public function i_should_see_text_matching(string $pattern): void
    {
        $pattern = trim(self::fixStepArgument($pattern), '"');
        self::assertPageContainsTextMatchingPattern($pattern);
    }

    /**
     * @Then /^(?:|I )should not see text matching (?P<pattern>"(?:[^"]|\\")*")$/
     */
    public function i_should_not_see_text_matching(string $pattern): void
    {
        $pattern = trim(self::fixStepArgument($pattern), '"');
        self::assertPageNotContainsTextMatchingPattern($pattern);
    }

    /**
     * @Then /^the response should contain "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function the_response_should_contain(string $text): void
    {
        $text = self::fixStepArgument($text);

        self::assertResponseShouldContain($text);
    }

    /**
     * @When I submit the :name form
     */
    public function i_submit_the_form_named(string $name): void
    {
        self::submitForm("form[name={$name}]");
    }

    /**
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with:$/
     * @When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)"$/
     */
    public function i_fill_field(string $field, string $value): void
    {
        self::fillField($field, $value);
    }

    /**
     * @When /^(?:|I )select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)"$/
     */
    public function i_select_option(string $select, string $option): void
    {
        self::selectOption($select, $option);
    }

    /**
     * @When /^(?:|I )check "(?P<option>(?:[^"]|\\")*)"$/
     */
    public function i_check_option(string $option): void
    {
        self::checkOption($option);
    }

    /**
     * @When /^(?:|I )uncheck "(?P<option>(?:[^"]|\\")*)"$/
     */
    public function i_uncheck_option(string $option): void
    {
        self::uncheckOption($option);
    }

    /**
     * @Then /^print last response$/
     */
    public function print_last_response(): void
    {
        self::printLastResponse();
    }

    /**
     * @Then /^print cookies$/
     */
    public function print_cookies(): void
    {
        self::printCookies();
    }

    /**
     * @Then /^(?:|I )should see (?P<num>\d+) "(?P<element>[^"]*)" elements?$/
     */
    protected function i_should_see_num_elements(int $count, string $selector): void
    {
        self::assertNumberOfElements($count, $selector);
    }

    protected static function fixStepArgument(string $argument): string
    {
        return str_replace('\\"', '"', $argument);
    }
}
