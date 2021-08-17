# Panther Actions
This project can be used for two reasons:
* Use Symfony Panther inside Behat.
* Extend Symfony Panther with a number of additional actions.

At first, we developed this package to replace Behat Mink. Mink has a history of slow updates and this is troublesome when you want to upgrade your project to the newest PHP version. We created a Behat Context with the same signature as a lot of often used Behat Mink steps. This can help you to replace Behat Mink with Symfony Panther, while still using the expressive syntax of Behat.

On a newer project we didn't want to use Behat and went with PHPUnit. There we started to use Symfony Panther, but quickly found out that we were missing a lot of actions that we got used to, while using Behat Mink. Therefore, we modified this package to be used inside a project without Behat as well.

## What is Symfony Panther?
> Panther is a convenient standalone library to scrape websites and to run end-to-end tests using real browsers.

[Symfony Panther](https://github.com/symfony/panther)

## Test coverage
A number of unit tests have been added to ensure the functionality. Not all functions are unit-tested. We will add more test coverage later on. For easy development and CI the docker and docker compose configurations have been added as well.

## Using this without Symfony Framework
Symfony Panther can be used without the Symfony framework. This package can be used without the Symfony framework as well.

## Installation
More instructions on how to install and configure Symfony Panther can be found [here](https://github.com/symfony/panther#installing-panther)

You'll need these environment variables for all components to work together:
```ini
PANTHER_APP_ENV=panther
PANTHER_NO_SANDBOX=1
PANTHER_CHROME_ARGUMENTS="--disable-dev-shm-usage"
PANTHER_ERROR_SCREENSHOT_DIR=./var/error-screenshots
PANTHER_EXTERNAL_BASE_URI=http://test-webserver:9080 # Change this to an address that serves your web-app
```

Further instructions can be found in the "How to" guides below.

## How to use
* [Integrate with PHPUnit](/docs/phpunit.md)
* [Integrate with Symfony](/docs/symfony.md)
* [Integrate with Behat](/docs/behat.md)

## Troubleshooting
If you followed the instructions on the Symfony Panther documentation then all should work. If not then you might have forgotten:
* Make sure the test webserver is running. For a docker example you can check the docker-compose.yaml file in this project.
* Make sure that you have either chrome or firefox installed. For a docker example you can check the Dockerfile in this project.
