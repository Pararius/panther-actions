# Using this together with Symfony
You can use these actions to login to Symfony. For this there is a separate Trait `PantherActions\Symfony\PantherLoginActions`.

## Example of logging in
```php
<?php

declare(strict_types=1);

namespace Tests\PantherActions;

use App\User;
use PantherActions\PantherActions;
use PantherActions\Symfony\PantherLoginActions;
use Symfony\Component\Panther\PantherTestCase;

final class PantherActionsTest extends PantherTestCase
{
    use PantherActions;
    use PantherLoginActions;

    /** @test */
    public function it_can_go_to_homepage(): void
    {
        self::loginUser(
            new User('john doe', 'john@doe.com')
        );
        self::goToHomepage();
        self::assertCurrentAddressMatches('/');
    }
}
```
