<?php echo '<?php'; ?>


declare(strict_types=1);

namespace <?php echo $namespace; ?>;

interface <?php echo $useCasePresenterClass; ?>

{
    public function present(<?php echo $useCaseResponseClass; ?> $response);
}
