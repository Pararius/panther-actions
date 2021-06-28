# Using this within Behat

The context is heavily inspired by [Behat Mink](http://mink.behat.org/en/latest/). It is not a drop-in replacement, but many of the actions are remade with Panther. The reason for us to create this package was because the development on Mink is often slow. This makes it more difficult for us to upgrade to the latest PHP versions. By relying on Symfony Panther (which is updated very fast) we hope to have a faster PHP upgrade path.

You can add this package to your project via:
```shell
composer require pararius/panther-actions --dev
```

You can include the context in behat via:
```yaml
default:
  formatters:
    pretty:
      paths: false
  suites:
    admin:
      paths:
        - '%paths.base%/tests/functional/'
      contexts:
        - PantherActions\Behat\PantherContext:
```

## Firefox instead of Chrome
If you prefer firefox then you can extend the class and override the `beforeSuite` function.


## Logging in
If you want to login via Symfony Panther then you can create your own context like this:

```php
use App\UserRepository;
use PantherActions\Behat\PantherContext;
use PantherActions\Symfony\PantherLoginActions;

class LoginContext extends PantherContext
    use PantherLoginActions;

    public function __construct(private UserRepository $users)
    {
    }

    /**
     * @When I log in with :email
     */
    public function i_log_in_with(string $email): void
    {
        // visit homepage to have a cookie set, this is needed to ensure the
        // right cookie parameters (like domain) are set by the application.
        // WebDriver will otherwise reject the cookie that we set below.
        $this->i_am_on_homepage();

        $user = $this->users->find($email);

        self::loginUser($user);
    }
```
