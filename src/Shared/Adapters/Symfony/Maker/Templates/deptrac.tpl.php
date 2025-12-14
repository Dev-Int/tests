deptrac:
    layers:
        - name: <?php echo $boundedContextName; ?>\Entities
          collectors:
              - type: classNameRegex
                value: '#<?php echo $boundedContextName; ?>\\Entities\\.*#'
        - name: <?php echo $boundedContextName; ?>\UseCases
          collectors:
              - type: classNameRegex
                value: '#<?php echo $boundedContextName; ?>\\UseCases\\.*#'
        - name: <?php echo $boundedContextName; ?>\Adapters
          collectors:
              - type: classNameRegex
                value: '#<?php echo $boundedContextName; ?>\\Adapters\\.*#'
    ruleset:
        <?php echo $boundedContextName; ?>\Entities:
        <?php echo $boundedContextName; ?>\UseCases:
            - <?php echo $boundedContextName; ?>\Entities
        <?php echo $boundedContextName; ?>\Adapters:
            - <?php echo $boundedContextName; ?>\Entities
            - <?php echo $boundedContextName; ?>\UseCases
    skip_violations: []
