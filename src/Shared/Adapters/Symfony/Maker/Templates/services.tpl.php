<?php echo '<?php'; ?>

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->private()
    ;

    $services->load(
        namespace: '<?php echo $namespace; ?>\\',
        resource: __DIR__ . '/../../../<?php echo $boundedContextName; ?>'
    )
        ->exclude(__DIR__ . '/../../../<?php echo $boundedContextName; ?>/{Frameworks,Entities,Tests}')
    ;
