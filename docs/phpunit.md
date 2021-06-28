# Using this within PHPUnit

You can integrate this quite easily with PHPUnit. All you need is to add this package to your project:
```shell
composer require pararius/panther-actions --dev
```

More instructions on how to install and configure Symfony Panther can be found [here](https://github.com/symfony/panther)

## Example on how to use this in PHPUnit

```php
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
}
```

## Troubleshooting
If you followed the instructions on the Symfony Panther documentation then all should work. If not then you might have forgotten:
* Make sure the test webserver is running. For a docker example you can check this [../docker-compose.yaml](docker-compose.yaml).
* Make sure you've added Panther to your `phpunit.xml.dist`. [../phpunit.xml.dist](example)
