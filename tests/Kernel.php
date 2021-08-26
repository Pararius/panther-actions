<?php

declare(strict_types=1);

namespace Tests\PantherActions;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private string $cachedProjectDir;

    public function getProjectDir(): string
    {
        return $this->cachedProjectDir ??= \dirname(__DIR__);
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
