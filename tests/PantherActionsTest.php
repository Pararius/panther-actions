<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Tests\PantherActions;

use PantherActions\PantherActions;
use RuntimeException;
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
    public function it_can_follow_link_with_context(): void
    {
        self::goTo('/page-with-context.html');
        self::assertCurrentAddressMatches('/page-with-context.html');

        self::followLink('Next page', '.wrapper');
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
    public function it_can_press_a_button_with_context(): void
    {
        self::goTo('/page-with-context.html');
        self::assertCurrentAddressMatches('/page-with-context.html');

        self::pressButton('Submit', '.wrapper');
        self::assertCurrentAddressContains('/other-page.html');
    }

    /** @test */
    public function it_can_assert_a_button_is_visible(): void
    {
        self::goToHomepage();
        self::assertButtonVisible('Submit');
    }

    /** @test */
    public function it_can_assert_a_button_is_visible_with_context(): void
    {
        self::goTo('/page-with-context.html');
        self::assertButtonVisible('Submit', '.wrapper');
    }

    /** @test */
    public function it_can_assert_a_button_is_not_visible(): void
    {
        self::goTo('/other-page.html');
        self::assertButtonNotVisible('Submit');
    }

    /** @test */
    public function it_can_assert_a_button_is_not_visible_with_context(): void
    {
        self::goTo('/page-with-context.html');
        self::assertButtonNotVisible('Other submit', '.wrapper');
    }

    /** @test */
    public function it_can_assert_a_link_is_visible(): void
    {
        self::goToHomepage();
        self::assertLinkVisible('Next page');
    }

    /** @test */
    public function it_can_assert_a_link_is_visible_with_context(): void
    {
        self::goTo('/page-with-context.html');
        self::assertLinkVisible('Next page', '.wrapper');
    }

    /** @test */
    public function it_can_assert_a_link_is_not_visible(): void
    {
        self::goTo('/other-page.html');
        self::assertLinkNotVisible('Next page');
    }

    /** @test */
    public function it_can_assert_a_link_is_not_visible_with_context(): void
    {
        self::goTo('/page-with-context.html');
        self::assertLinkNotVisible('Other link', '.wrapper');
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

    /** @test */
    public function it_fills_a_textfield_by_label(): void
    {
        self::goTo('/form.html');
        self::assertCurrentAddressMatches('/form.html');

        $value = 'Value of the textfield';
        self::fillField('A textfield', $value);
        self::assertFormValue('form', 'a-textfield', $value);
    }

    /** @test */
    public function it_fills_a_textfield_by_placeholder(): void
    {
        self::goTo('/form.html');
        self::assertCurrentAddressMatches('/form.html');

        $value = 'Value of the textfield';
        self::fillField('textfield with placeholder', $value);
        self::assertFormValue('form', 'placeholder', $value);
    }

    /** @test */
    public function it_can_fill_a_field_by_nested_label(): void
    {
        self::goTo('/form-label-nested-text.html');

        $value = 'Value of the textfield';
        self::fillField('Textfield', $value);
        self::assertFormValue('form', 'fld', $value);
    }

    /** @test */
    public function it_cannot_fill_a_field_by_label_without_id(): void
    {
        self::goTo('/form-label-without-id.html');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not fill form field');

        self::fillField('Textfield', 'foo');
    }
}
