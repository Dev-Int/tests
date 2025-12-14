parameters:

services:
    _defaults:
        autowire: true
        autoconfigure: true

    <?php echo $namespace; ?>\:
        resource: '%kernel.project_dir%/src/<?php echo $moduleName; ?>/*'
        exclude: '%kernel.project_dir%/src/<?php echo $moduleName; ?>/{Frameworks,Entites,Tests}'
