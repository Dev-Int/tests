<?php echo '<?php'; ?>


declare(strict_types=1);

namespace <?php echo $namespace; ?>;

final class <?php echo $useCaseName; ?>

{
    public function execute(<?php echo $useCaseRequestClass; ?> $request, <?php echo $useCasePresenterClass; ?> $presenter): void
    {
        $presenter->present(new <?php echo $useCaseResponseClass; ?>());
    }
}
