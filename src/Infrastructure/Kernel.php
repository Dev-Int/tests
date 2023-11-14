<?php

namespace Infrastructure;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.yaml');
        $container->import('../config/{packages}/' . $this->environment . '/*.yaml');

        // Dynamic packages configuration
        $container->import('../src/*/Frameworks/config/{packages}/*.{yaml,php}');
        $container->import('../src/Component/*/config/{packages}/*.{yaml,php}');

        if (is_file(\dirname(__DIR__) . '/config/services.yaml')) {
            $container->import('../config/services.yaml');
            $container->import('../config/{services}_' . $this->environment . '.yaml');
        } else {
            $container->import('../config/{services}.php');
        }

        // Dynamic services configuration
        $container->import('../src/*/Frameworks/config/services.{yaml,php}');
        $container->import('../src/*/Frameworks/config/{services}/*.yaml');
        $container->import('../src/*/Frameworks/config/packages/' . $this->environment . '/*.yaml');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/' . $this->environment . '/*.yaml');
        $routes->import('../config/{routes}/*.yaml');

        if (is_file(\dirname(__DIR__) . '/config/routes.yaml')) {
            $routes->import('../config/routes.yaml');
        } else {
            $routes->import('../config/{routes}.php');
        }

        // Dynamic module routing configuration
        $routes->import('../src/*/Frameworks/config/routes.yaml');
        $routes->import('../src/*/Frameworks/config/{routes}/*.yaml');
    }
}
