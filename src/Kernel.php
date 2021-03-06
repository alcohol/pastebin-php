<?php

declare(strict_types=1);

namespace Paste;

use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public const ENVIRONMENTS = ['test', 'dev', 'prod'];

    public function __construct(string $environment, bool $debug)
    {
        if (!\in_array($environment, self::ENVIRONMENTS, true)) {
            throw new RuntimeException(sprintf('Unsupported environment "%s", expected one of: %s', $environment, implode(', ', self::ENVIRONMENTS)));
        }

        parent::__construct($environment, $debug);
    }

    public function isDevelopment(): bool
    {
        return 'dev' === $this->getEnvironment();
    }

    public function isTesting(): bool
    {
        return 'test' === $this->getEnvironment();
    }

    public function isProduction(): bool
    {
        return 'prod' === $this->getEnvironment();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/'.$this->environment.'/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/services.yaml')) {
            $container->import('../config/services.yaml');
            $container->import('../config/{services}_'.$this->environment.'.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/services.php')) {
            (require $path)($container->withPath($path), $this);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/'.$this->environment.'/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__).'/config/routes.yaml')) {
            $routes->import('../config/routes.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'/config/routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }
    }
}
