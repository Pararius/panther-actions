<?php

declare(strict_types=1);

namespace Tests\PantherActions;

use PantherActions\PantherActions;
use Symfony\Component\Panther\PantherTestCase;

final class PantherActionsTest extends PantherTestCase
{
    use PantherActions;

    /** @test */
    public function it_can_go_to_homepage(): void
    {
        self::goToHomepage();
        self::assertCurrentAddressMatches('/');
    }

    /** @test */
    public function it_can_go_to_another_page(): void
    {
        self::goToHomepage();
        self::assertCurrentAddressMatches('/');

        self::goTo('other-page.html');
        self::assertCurrentAddressMatches('/other-page.html');
    }

    /** @test */
    public function it_can_match_current_address_on_pattern(): void
    {
        self::goTo('other-page.html');
        self::assertCurrentAddressMatchesPattern('#page\.html$#');
    }

    /** @test */
    public function it_can_match_current_address_contains(): void
    {
        self::goTo('other-page.html');
        self::assertCurrentAddressContains('page');
    }

    /** @test */
    public function it_can_assert_on_homepage(): void
    {
        self::goToHomepage();
        self::assertOnHomepage();
    }

    /** @test */
    public function it_can_follow_link(): void
    {
        self::goToHomepage();
        self::assertCurrentAddressMatches('/');

        self::followLink('Next page');
        self::assertCurrentAddressMatches('/other-page.html');
    }

    /** @test */
    public function it_can_press_a_button(): void
    {
        self::goToHomepage();
        self::assertCurrentAddressMatches('/');

        self::pressButton('Submit');
        self::assertCurrentAddressContains('/other-page.html');
    }

    /** @test */
    public function it_can_assert_a_button_is_visible(): void
    {
        self::goToHomepage();
        self::assertButtonVisible('Submit');
    }

    /** @test */
    public function it_can_assert_a_button_is_not_visible(): void
    {
        self::goTo('/other-page.html');
        self::assertButtonNotVisible('Submit');
    }

    /** @test */
    public function it_can_assert_a_link_is_visible(): void
    {
        self::goToHomepage();
        self::assertLinkVisible('Next page');
    }

    /** @test */
    public function it_can_assert_a_link_is_not_visible(): void
    {
        self::goTo('/other-page.html');
        self::assertLinkNotVisible('Next page');
    }

    /** @test */
    public function it_can_assert_a_page_contains_text(): void
    {
        self::goToHomepage();
        self::assertPageContainsText('Page body');
    }

    /** @test */
    public function it_can_assert_a_page_does_not_contain_text(): void
    {
        self::goToHomepage();
        self::assertPageDoesNotContainText('Something else');
    }

    /** @test */
    public function it_can_assert_a_page_contains_text_matching_pattern(): void
    {
        self::goToHomepage();
        self::assertPageContainsTextMatchingPattern('#Page#');
    }

    /** @test */
    public function it_can_assert_a_page_does_not_contain_text_matching_pattern(): void
    {
        self::goToHomepage();
        self::assertPageNotContainsTextMatchingPattern('#Something#');
    }

    /** @test */
    public function it_can_assert_a_response_contains(): void
    {
        self::goToHomepage();
        self::assertResponseShouldContain('<h1>');
    }

    /** @test */
    public function it_can_submit_a_form(): void
    {
        self::goToHomepage();
        self::assertCurrentAddressMatches('/');

        self::submitTheFormNamed('form');
        self::assertCurrentAddressContains('/other-page.html');
    }
}
