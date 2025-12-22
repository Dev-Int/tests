<?php

declare(strict_types=1);

/*
 * This file is part of the Tests package.
 *
 * (c) Dev-Int Création <info@developpement-interessant.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shared\Adapters\Symfony\Maker;

use Shared\Adapters\Symfony\Maker\Model\BoundedContext;
use Shared\Adapters\Symfony\Maker\Model\UseCase;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

final class MakeUseCaseCreate extends AbstractMaker
{
    private const string COMMAND_NAME = 'make:use-case:create';

    public static function getCommandName(): string
    {
        return self::COMMAND_NAME;
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new use case with the minimal classes in the specified bounded context.';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('bounded context', InputArgument::OPTIONAL, 'Enter the bounded context\'s name.', '')
            ->addArgument('use-case', InputArgument::OPTIONAL, 'Enter the use case\'s name.', '')
        ;
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        /** @var string $boundedContextName */
        $boundedContextName = $input->getArgument('bounded context') ?? '';
        if ('' === $boundedContextName) {
            /** @var string $boundedContextName */
            $boundedContextName = $io->ask('Enter the name of the bounded context ?');
        }

        /** @var string $useCaseName */
        $useCaseName = $input->getArgument('use-case') ?? '';
        if ('' === $useCaseName) {
            /** @var string $useCaseName */
            $useCaseName = $io->ask('Enter the name of the use case ?');
        }

        $module = BoundedContext::new(
            $boundedContextName,
            $generator->getRootNamespace(),
            $generator->getRootDirectory(),
            'src'
        );

        $useCase = $module->createUseCase($useCaseName);

        $generator->generateFile(
            $useCase->absoluteFilenamePath(),
            UseCase::TEMPLATE_FILENAME,
            [
                'namespace' => $useCase->namespace()->toString(),
                'useCaseName' => $useCase->name(),
                'useCaseRequestClass' => $useCase->requestClassName(),
                'useCaseResponseClass' => $useCase->responseClassName(),
            ]
        );

        $generator->generateFile(
            $useCase->absoluteRequestClassFilenamePath(),
            UseCase::REQUEST_TEMPLATE_FILENAME,
            [
                'namespace' => $useCase->namespace()->toString(),
                'useCaseRequestClass' => $useCase->requestClassName(),
            ]
        );

        $generator->generateFile(
            $useCase->absoluteResponseClassFilenamePath(),
            UseCase::RESPONSE_TEMPLATE_FILENAME,
            [
                'namespace' => $useCase->namespace()->toString(),
                'useCaseResponseClass' => $useCase->responseClassName(),
            ]
        );

        $generator->generateFile(
            $useCase->absoluteTestClassFilenamePath(),
            UseCase::TEST_TEMPLATE_FILENAME,
            [
                'testNamespace' => $useCase->testNamespace()->toString(),
                'useCaseNamespace' => $useCase->namespace()->toString(),
                'useCaseName' => $useCase->name(),
                'useCaseRequestClass' => $useCase->requestClassName(),
                'useCaseResponseClass' => $useCase->responseClassName(),
            ]
        );

        $generator->writeChanges();
    }
}
