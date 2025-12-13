deptrac:
    layers:
        - name: <?php echo $moduleName; ?>\Entities
          collectors:
              - type: className
                regex: <?php echo $moduleName; ?>\\Entities\\.*
        - name: <?php echo $moduleName; ?>\UseCases
          collectors:
              - type: className
                regex: <?php echo $moduleName; ?>\\UseCases\\.*
        - name: <?php echo $moduleName; ?>\Adapters
          collectors:
              - type: className
                regex: <?php echo $moduleName; ?>\\Adapters\\.*
    ruleset:
        <?php echo $moduleName; ?>\Entities:
        <?php echo $moduleName; ?>\UseCases:
            - <?php echo $moduleName; ?>\Entities
        <?php echo $moduleName; ?>\Adapters:
            - <?php echo $moduleName; ?>\Entities
            - <?php echo $moduleName; ?>\UseCases
    skip_violations: []
