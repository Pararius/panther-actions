<?php

declare(strict_types=1);

namespace PantherActions;

use Symfony\Component\Panther\Client as PantherClient;
use function assert;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\Assert as PHPUnit;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\Panther\PantherTestCase;

trait PantherActions
{
    protected static function goToHomepage(): void
    {
        self::goTo('/');
    }

    protected static function goTo(string $page): void
    {
        $page = '/' . ltrim($page, '/');

        PantherTestCase::createPantherClient()->request('GET', $page);
    }

    protected static function assertCurrentAddressMatches(string $page): void
    {
        assert(PantherTestCase::$baseUri !== null);
        $startUrl = rtrim(PantherTestCase::$baseUri, '/') . '/';
        $page = !str_starts_with($page, 'http') ? $startUrl . ltrim($page, '/') : $page;

        PHPUnit::assertSame(
            $page,
            PantherTestCase::createPantherClient()->getCurrentURL()
        );
    }

    protected static function assertCurrentAddressMatchesPattern(string $pattern): void
    {
        PHPUnit::assertMatchesRegularExpression(
            $pattern,
            PantherTestCase::createPantherClient()->getCurrentURL()
        );
    }

    protected static function assertCurrentAddressContains(string $path): void
    {
        PHPUnit::assertStringContainsString(
            $path,
            PantherTestCase::createPantherClient()->getCurrentURL()
        );
    }

    protected static function assertOnHomepage(): void
    {
        self::assertCurrentAddressMatches('/');
    }

    protected static function followLink(string $linkText): void
    {
        $client = PantherTestCase::createPantherClient();

        $link = self::crawler()
            ->filterXPath(
                sprintf(
                    <<<'XPATH'
                        descendant-or-self::a[
                            @id=%2$s or
                            @class=%2$s or
                            contains(concat(' ', normalize-space(string(.)), ' '), %1$s) or
                            contains(concat(' ', normalize-space(string(@title)), ' '), %1$s) or
                            ./img[contains(concat(' ', normalize-space(string(@alt)), ' '), %1$s)]
                        ]
                        XPATH,
                    Crawler::xpathLiteral(' ' . $linkText . ' '),
                    Crawler::xpathLiteral($linkText)
                )
            )
            ->link()
        ;
        $client->click($link);
    }

    protected static function pressButton(string $button): void
    {
        $form = self::crawler()->filterXPath(
            sprintf(
                <<<'XPATH'
                    //form[//button[text()[contains(concat(' ', normalize-space(.), ' '), %1$s)]]]
                    |
                    //button[text()[contains(concat(' ', normalize-space(.), ' '), %1$s)]]
                    XPATH,
                Crawler::xpathLiteral(' ' . $button . ' ')
            )
        );
        $client = PantherTestCase::createPantherClient();
        if ($form->nodeName() === 'form') {
            $client->submit($form->form());
        } else {
            $client->executeScript(
                <<<JS
                      [...document.querySelectorAll('button')]
                      .filter((element) => element.innerText.trim() === '{$button}')
                      [0].click()
                    JS
            );
        }
    }

    protected static function assertButtonVisible(string $button): void
    {
        PHPUnit::assertNotCount(
            0,
            self::crawler()->selectButton($button)
        );
    }

    protected static function assertButtonNotVisible(string $button): void
    {
        PHPUnit::assertCount(
            0,
            self::crawler()->selectButton($button)
        );
    }

    protected static function assertLinkVisible(string $link): void
    {
        PHPUnit::assertNotCount(
            0,
            self::crawler()->selectLink($link)
        );
    }

    protected static function assertLinkNotVisible(string $link): void
    {
        PHPUnit::assertCount(
            0,
            self::crawler()->selectLink($link)
        );
    }

    protected static function assertPageContainsText(string $text): void
    {
        $body = self::bodyText();
        $body = preg_replace('#\R#', ' ', $body);
        assert($body !== null);
        PHPUnit::assertStringContainsStringIgnoringCase($text, $body);
    }

    protected static function assertPageDoesNotContainText(string $text): void
    {
        $body = self::bodyText();
        PHPUnit::assertStringNotContainsStringIgnoringCase($text, $body);
    }

    protected static function assertPageContainsTextMatchingPattern(string $pattern): void
    {
        $body = self::bodyText();
        PHPUnit::assertMatchesRegularExpression($pattern, $body);
    }

    protected static function assertPageNotContainsTextMatchingPattern(string $pattern): void
    {
        $body = self::bodyText();
        PHPUnit::assertDoesNotMatchRegularExpression($pattern, $body);
    }

    protected static function assertResponseShouldContain(string $text): void
    {
        $html = PantherTestCase::createPantherClient()->getPageSource();
        PHPUnit::assertStringContainsStringIgnoringCase($text, $html);
    }

    public static function assertPageTitleContainsIgnoringCase(string $expectedTitle): void
    {
        $title = PantherTestCase::createPantherClient()->getTitle();
        self::assertStringContainsStringIgnoringCase($expectedTitle, $title);
    }

    protected static function submitTheFormNamed(string $name): void
    {
        self::submitForm("form[name={$name}]");
    }

    protected static function fillField(string $fieldText, string $value, string $legend = null): void
    {
        $field = self::findFormField($fieldText, $legend);
        $field
            ->filterXPath('ancestor::form')
            ->form([
                $field->attr('name') => $value,
            ])
        ;
    }

    protected static function selectOption(string $select, string $option, string $legend = null): void
    {
        $field = self::findFormField($select, $legend);
        $form = $field->filterXPath('ancestor::form')->form();
        $name = $field->attr('name');
        PHPUnit::assertInstanceOf(ChoiceFormField::class, $form[$name]);
        assert($form[$name] instanceof ChoiceFormField);
        $form[$name]->select($option);
    }

    protected static function checkOption(string $option, string $legend = null): void
    {
        $field = self::findFormField($option, $legend);
        $form = $field->filterXPath('ancestor::form')->form();
        $name = $field->attr('name');
        PHPUnit::assertInstanceOf(ChoiceFormField::class, $form[$name]);
        assert($form[$name] instanceof ChoiceFormField);
        $value = $field->attr('value');
        assert($value !== null);
        $form[$name]->select($value);
    }

    protected static function uncheckOption(string $option, string $legend = null): void
    {
        $field = self::findFormField($option, $legend);
        $form = $field->filterXPath('ancestor::form')->form();
        $name = $field->attr('name');
        PHPUnit::assertInstanceOf(ChoiceFormField::class, $form[$name]);
        assert($form[$name] instanceof ChoiceFormField);
        $form[$name]->untick();
    }

    protected static function printLastResponse(): void
    {
        $client = PantherTestCase::createPantherClient();
        echo $client->getCurrentUrl() . "\n\n" .
            $client->getPageSource()
        ;
    }

    protected static function printCookies(): void
    {
        $client = PantherTestCase::createPantherClient();
        print_r($client->getCookieJar()->all());
    }

    protected static function assertNumberOfElements(int $count, string $selector): void
    {
        $elements = PantherTestCase::createPantherClient()
            ->findElements(WebDriverBy::cssSelector($selector))
        ;
        PHPUnit::assertCount($count, $elements);
    }

    protected static function submitForm(string $selector): void
    {
        $client = PantherTestCase::createPantherClient();
        $client->executeScript("document.querySelector('{$selector}').formNoValidate=true");
        $form = self::crawler()
            ->filter($selector)
            ->form()
        ;
        $client->submit($form);
    }

    protected static function findFormField(string $fieldText, string $legend = null): Crawler
    {
        $xpath = <<<'XPATH'
            [
                contains(concat(' ', normalize-space(text()), ' '), %1$s) or
                contains(concat(' ', normalize-space(string(@value)), ' '), %1$s) or
                @id=%2$s or
                @name=%2$s
            ]
            XPATH;

        $fieldset = null;
        if ($legend !== null) {
            $fieldset = self::crawler()
                ->filterXPath(
                    sprintf(
                        '//legend' . $xpath . '/ancestor::fieldset',
                        Crawler::xpathLiteral(' ' . $legend . ' '),
                        Crawler::xpathLiteral($legend),
                    )
                )
            ;
        }

        foreach (['//input', '//label', '//select', '//option'] as $tag) {
            $parts[] = $tag . $xpath;
        }

        $field = ($fieldset ?? self::crawler())
            ->filterXPath(
                sprintf(
                    implode(' | ', $parts),
                    Crawler::xpathLiteral(' ' . $fieldText . ' '),
                    Crawler::xpathLiteral($fieldText),
                )
            )
        ;

        assert($field instanceof Crawler);

        // Find input field connected to label.
        if ($field->getTagName() === 'label') {
            $field = self::crawler()
                ->filter('#' . $field->attr('for'))
            ;
        }

        return $field;
    }

    protected static function crawler(): Crawler
    {
        return PantherTestCase::createPantherClient()->getCrawler();
    }

    protected static function bodyText(): string
    {
        return self::crawler()
            ->filter('body')
            ->text()
            ;
    }
}
