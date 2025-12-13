<?php echo '<?php'; ?>


declare(strict_types=1);

namespace <?php echo $testNamespace; ?>;

use <?php echo $useCaseNamespace; ?>\<?php echo $useCaseName; ?>;
use <?php echo $useCaseNamespace; ?>\<?php echo $useCasePresenterClass; ?>;
use <?php echo $useCaseNamespace; ?>\<?php echo $useCaseRequestClass; ?>;
use <?php echo $useCaseNamespace; ?>\<?php echo $useCaseResponseClass; ?>;
use PHPUnit\Framework\TestCase;

/**
 * @group unitTest
 */
final class <?php echo $useCaseName; ?>Test extends TestCase
{
    /**
     * @test
     */
    public function itShould<?php echo $useCaseName; ?>(): void
    {
        $request = new <?php echo $useCaseRequestClass; ?>();
        $useCase = new <?php echo $useCaseName; ?>();
        $expectedResponse = new <?php echo $useCaseResponseClass; ?>();

        $presenter = $this->createMock(<?php echo $useCasePresenterClass; ?>::class);
        $presenter
            ->expects($this->once())
            ->method('present')
            ->with($expectedResponse);

        $useCase->execute($request, $presenter);
    }
}
