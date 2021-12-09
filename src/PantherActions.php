<?php

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace PantherActions;

use Facebook\WebDriver\Exception\NoSuchElementException;
use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use RuntimeException;
use Symfony\Component\Panther\Client;
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

        self::client()->request('GET', $page);
    }

    protected static function assertCurrentAddressMatches(string $page): void
    {
        self::assertNotNull(PantherTestCase::$baseUri);

        $startUrl = rtrim(PantherTestCase::$baseUri, '/') . '/';
        $page = !str_starts_with($page, 'http') ? $startUrl . ltrim($page, '/') : $page;

        Assert::assertSame(
            $page,
            self::client()->getCurrentURL()
        );
    }

    protected static function assertCurrentAddressMatchesPattern(string $pattern): void
    {
        Assert::assertMatchesRegularExpression(
            $pattern,
            self::client()->getCurrentURL()
        );
    }

    protected static function assertCurrentAddressContains(string $path): void
    {
        Assert::assertStringContainsString(
            $path,
            self::client()->getCurrentURL()
        );
    }

    protected static function assertOnHomepage(): void
    {
        self::assertCurrentAddressMatches('/');
    }

    protected static function followLink(string $linkText, string $contextSelector = null): void
    {
        $client = self::client();

        $crawler = self::crawlerBySelector($contextSelector);

        $link = $crawler
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

    protected static function pressButton(string $button, string $contextSelector = null): void
    {
        $crawler = self::crawlerBySelector($contextSelector);

        $form = $crawler->filterXPath(
            sprintf(
                <<<'XPATH'
                    //form[//button[text()[contains(concat(' ', normalize-space(.), ' '), %1$s)]]]
                    |
                    //button[text()[contains(concat(' ', normalize-space(.), ' '), %1$s)]]
                    XPATH,
                Crawler::xpathLiteral(' ' . $button . ' ')
            )
        );
        $client = self::client();
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

    protected static function assertButtonVisible(string $button, string $contextSelector = null): void
    {
        $crawler = self::crawlerBySelector($contextSelector);
        Assert::assertNotCount(
            0,
            $crawler->selectButton($button)
        );
    }

    protected static function assertButtonNotVisible(string $button, string $contextSelector = null): void
    {
        $crawler = self::crawlerBySelector($contextSelector);
        Assert::assertCount(
            0,
            $crawler->selectButton($button)
        );
    }

    protected static function assertLinkVisible(string $link, string $contextSelector = null): void
    {
        $crawler = self::crawlerBySelector($contextSelector);
        Assert::assertNotCount(
            0,
            $crawler->selectLink($link)
        );
    }

    protected static function assertLinkNotVisible(string $link, string $contextSelector = null): void
    {
        $crawler = self::crawlerBySelector($contextSelector);
        Assert::assertCount(
            0,
            $crawler->selectLink($link)
        );
    }

    protected static function assertPageContainsText(string $text): void
    {
        $body = self::bodyText();
        $body = preg_replace('#\R#', ' ', $body);
        \assert($body !== null);
        Assert::assertStringContainsStringIgnoringCase($text, $body);
    }

    protected static function assertPageDoesNotContainText(string $text): void
    {
        $body = self::bodyText();
        Assert::assertStringNotContainsStringIgnoringCase($text, $body);
    }

    protected static function assertPageContainsTextMatchingPattern(string $pattern): void
    {
        $body = self::bodyText();
        Assert::assertMatchesRegularExpression($pattern, $body);
    }

    protected static function assertPageNotContainsTextMatchingPattern(string $pattern): void
    {
        $body = self::bodyText();
        Assert::assertDoesNotMatchRegularExpression($pattern, $body);
    }

    protected static function assertResponseShouldContain(string $text): void
    {
        $html = self::client()->getPageSource();
        Assert::assertStringContainsStringIgnoringCase($text, $html);
    }

    protected static function assertPageTitleContainsIgnoringCase(string $expectedTitle): void
    {
        $title = self::client()->getTitle();
        self::assertStringContainsStringIgnoringCase($expectedTitle, $title);
    }

    protected static function submitTheFormNamed(string $name): void
    {
        self::submitForm("form[name={$name}]");
    }

    protected static function fillField(string $fieldText, string $value, string $legend = null, string $contextSelector = null): void
    {
        try {
            $field = self::findFormField($fieldText, $legend, $contextSelector);
            $field
                ->filterXPath('ancestor::form')
                ->form([
                    $field->attr('name') => $value,
                ])
            ;
        } catch (InvalidArgumentException | NoSuchElementException $exception) {
            throw new RuntimeException(
                "Could not fill form field \"{$fieldText}\"",
                $exception->getCode(),
                $exception
            );
        }
    }

    protected static function selectOption(string $select, string $option, string $legend = null, string $contextSelector = null): void
    {
        $field = self::findFormField($select, $legend, $contextSelector);
        $form = $field->filterXPath('ancestor::form')->form();
        $name = $field->attr('name');
        Assert::assertInstanceOf(ChoiceFormField::class, $form[$name]);
        \assert($form[$name] instanceof ChoiceFormField);
        $form[$name]->select($option);
    }

    protected static function checkOption(string $option, string $legend = null, string $contextSelector = null): void
    {
        $field = self::findFormField($option, $legend, $contextSelector);
        $form = $field->filterXPath('ancestor::form')->form();
        $name = $field->attr('name');
        Assert::assertInstanceOf(ChoiceFormField::class, $form[$name]);
        \assert($form[$name] instanceof ChoiceFormField);
        $value = $field->attr('value');
        \assert($value !== null);
        $form[$name]->select($value);
    }

    protected static function uncheckOption(string $option, string $legend = null, string $contextSelector = null): void
    {
        $field = self::findFormField($option, $legend, $contextSelector);
        $form = $field->filterXPath('ancestor::form')->form();
        $name = $field->attr('name');
        Assert::assertInstanceOf(ChoiceFormField::class, $form[$name]);
        \assert($form[$name] instanceof ChoiceFormField);
        $form[$name]->untick();
    }

    protected static function printLastResponse(): void
    {
        $client = self::client();
        echo $client->getCurrentUrl() . "\n\n" .
            $client->getPageSource()
        ;
    }

    protected static function printCookies(): void
    {
        $client = self::client();
        /** @noinspection ForgottenDebugOutputInspection */
        print_r($client->getCookieJar()->all());
    }

    protected static function assertNumberOfElements(int $count, string $selector): void
    {
        $elements = self::crawlerBySelector($selector);
        Assert::assertCount($count, $elements);
    }

    protected static function submitForm(string $selector): void
    {
        $client = self::client();
        $client->executeScript("document.querySelector('{$selector}').formNoValidate=true");
        $form = self::crawlerBySelector($selector)
            ->form()
        ;
        $client->submit($form);
    }

    protected static function findFormField(string $fieldText, string $legend = null, string $contextSelector = null): Crawler
    {
        $xpath = <<<'XPATH'
            [
                contains(concat(' ', normalize-space(string(.)), ' '), %1$s) or
                contains(concat(' ', normalize-space(string(@value)), ' '), %1$s) or
                @id=%2$s or
                @name=%2$s or
                @placeholder=%2$s
            ]
            XPATH;

        $crawler = self::crawlerBySelector($contextSelector);

        if ($legend !== null) {
            $crawler = $crawler
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

        $field = $crawler
            ->filterXPath(
                sprintf(
                    implode(' | ', $parts),
                    Crawler::xpathLiteral(' ' . $fieldText . ' '),
                    Crawler::xpathLiteral($fieldText),
                )
            )
        ;

        \assert($field instanceof Crawler);

        // Find input field connected to label.
        if ($field->nodeName() === 'label' && $id = $field->attr('for')) {
            $field = self::crawler()->filter("#{$id}");
        }

        return $field;
    }

    protected static function crawler(): Crawler
    {
        return self::client()->getCrawler();
    }

    protected static function crawlerBySelector(string $selector = null): Crawler
    {
        if ($selector === null) {
            return self::crawler();
        }

        return self::crawler()->filter($selector);
    }

    protected static function bodyText(): string
    {
        return self::crawler()
            ->filter('body')
            ->text()
        ;
    }

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $kernelOptions
     * @param array<string, mixed> $managerOptions
     */
    protected static function client(array $options = [], array $kernelOptions = [], array $managerOptions = []): Client
    {
        return PantherTestCase::createPantherClient($options, $kernelOptions, $managerOptions);
    }
}
