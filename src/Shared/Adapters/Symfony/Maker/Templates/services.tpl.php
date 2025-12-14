parameters:

services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false

    <?php echo $namespace; ?>\:
        resource: '%kernel.project_dir%/src/<?php echo $boundedContextName; ?>/*'
        exclude:
            - '%kernel.project_dir%/src/<?php echo $boundedContextName; ?>/{Frameworks,Entities,Tests}'
