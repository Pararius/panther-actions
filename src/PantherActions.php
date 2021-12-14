<?php

declare(strict_types=1);

namespace PantherActions;

use function array_map;
use function implode;
use PHPUnit\Framework\Assert;
use RuntimeException;
use function sprintf;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\Panther\PantherTestCase;
use function trim;

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
        $field = self::findFormField($fieldText, $legend, $contextSelector);
        $field
            ->filterXPath('ancestor::form')
            ->form([
                $field->attr('name') => $value,
            ])
        ;
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
        // create crawler, optionally start by context
        $crawler = self::crawlerBySelector($contextSelector);

        // if legend is given, search for that and get its fieldset
        if ($legend !== null) {
            $crawler = $crawler->filterXPath(
                self::formFieldXpath('descendant-or-self::legend', $legend) . '/ancestor::fieldset'
            );
        }

        // find form labels or elements with the given `$fieldText` as id, name, text, or placeholder
        $field = $crawler->filterXPath(
            implode(' | ', array_map(
                static fn (string $tag): string => self::formFieldXpath("descendant-or-self::{$tag}", $fieldText),
                ['label', 'input', 'select', 'option', 'textarea'],
            ))
        );

        $foundFieldTag = $field->getElement(0)?->getTagName();

        // Find input field connected to label.
        if ($foundFieldTag === 'label') {
            if ($id = trim($field->attr('for'))) {
                // filter by id
                $field = self::crawler()->filter("#{$id}");
            } else {
                // search for input types in descendants
                $field = $field->filterXPath(
                    implode(' | ', array_map(
                        static fn (string $tag): string => "descendant::{$tag}",
                        ['input | select | option | textarea'],
                    ))
                );
            }
        }

        if ($field->count() === 0) {
            $message = ($foundFieldTag === 'label')
                ? "Label found for form field \"{$fieldText}\", but no input is associated with it"
                : "Could not find form field \"{$fieldText}\""
            ;

            if ($contextSelector) {
                $message .= " in selector: {$contextSelector}";
            }

            throw new RuntimeException($message);
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

    protected static function formFieldXpath(string $fieldType, string $text): string
    {
        return sprintf(
            <<<'XPATH'
                %1$s[
                    contains(concat(' ', normalize-space(string(.)), ' '), %2$s) or
                    contains(concat(' ', normalize-space(string(@value)), ' '), %2$s) or
                    @id=%3$s or
                    @name=%3$s or
                    @placeholder=%3$s
                ]
                XPATH,
            $fieldType,
            Crawler::xpathLiteral(' ' . $text . ' '),
            Crawler::xpathLiteral($text),
        );
    }
}
