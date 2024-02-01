<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function configureContainer(ContainerConfigurator $container): void
    {
        $configDir = $this->getConfigDir();

        $container->import($configDir . '/{packages}/*.{php,yaml}');

        if (is_file($configDir . '/services.yaml')) {
            $container->import($configDir . '/services.yaml');
        } else {
            $container->import($configDir . '/{services}.php');
        }

        // Dynamic services configuration
        $container->import($this->getProjectDir() . '/src/*/Frameworks/config/services.{yaml,php}');
        $container->import($this->getProjectDir() . '/src/*/Frameworks/config/{services}/*.yaml');
        $container->import($this->getProjectDir() . '/src/*/Frameworks/config/packages/*.yaml');
    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
        $configDir = $this->getConfigDir();

        $routes->import($configDir . '/{routes}/' . $this->environment . '/*.{php,yaml}');
        $routes->import($configDir . '/{routes}/*.{php,yaml}');

        if (is_file($configDir . '/routes.yaml')) {
            $routes->import($configDir . '/routes.yaml');
        } else {
            $routes->import($configDir . '/{routes}.php');
        }

        if (false !== ($fileName = (new \ReflectionObject($this))->getFileName())) {
            $routes->import($fileName, 'annotation');
        }

        // Dynamic module routing configuration
        $routes->import($this->getProjectDir() . '/src/*/Frameworks/config/routes.yaml');
        $routes->import($this->getProjectDir() . '/src/*/Frameworks/config/{routes}/*.yaml');
    }
}
