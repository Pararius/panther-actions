<?php

declare(strict_types=1);

namespace PantherActions\Symfony;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Security\Core\User\UserInterface;

trait PantherLoginActions
{
    protected static function loginUser(UserInterface $user, string $firewallContext = 'main'): void
    {
        $client = PantherTestCase::createPantherClient();
        $client->request('GET', '/');

        $token = new TestBrowserToken($user->getRoles(), $user, $firewallContext);
        $token->setAuthenticated(true);
        $session = KernelTestCase::getContainer()->get('session.factory')?->createSession();

        if ($session === null) {
            throw new SessionNotFoundException();
        }

        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }
}
