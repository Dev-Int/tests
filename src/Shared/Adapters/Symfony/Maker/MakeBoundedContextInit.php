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
use Shared\Adapters\Symfony\Maker\Model\Path;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;

final class MakeBoundedContextInit extends AbstractMaker
{
    private const string COMMAND_NAME = 'make:bounded-context:init';

    public static function getCommandName(): string
    {
        return self::COMMAND_NAME;
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new bounded context with the minimal directory structure and configuration.';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('bounded-context', InputArgument::OPTIONAL, 'Enter the bounded context\'s name.')
        ;
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        /** @var string $boundedContextName */
        $boundedContextName = $input->getArgument('bounded-context') ?? '';
        if ('' === $boundedContextName) {
            /** @var string $boundedContextName */
            $boundedContextName = $io->ask('Name of the bounded context ?');
        }

        $boundedContext = BoundedContext::new(
            $boundedContextName,
            $generator->getRootNamespace(),
            $generator->getRootDirectory(),
            'src'
        );

        $generator->generateFile(
            $boundedContext->absolutePath()->append('Frameworks', 'deptrac.yaml')->toString(),
            __DIR__ . '/Templates/deptrac.tpl.php',
            [
                'boundedContextName' => $boundedContext->name(),
            ]
        );

        $generator->generateFile(
            $boundedContext->absolutePath()->append('Frameworks', 'config', 'services.yaml')->toString(),
            __DIR__ . '/Templates/services.tpl.php',
            [
                'boundedContextName' => $boundedContext->name(),
                'namespace' => $boundedContext->namespace()->toString(),
            ]
        );

        $this->addEntryForDeptrac(
            $generator,
            Path::fromString($generator->getRootDirectory())->append('deptrac.yaml')->toString(),
            $boundedContext->relativePath()->append('Frameworks')->append('deptrac.yaml')->toString()
        );

        $generator->writeChanges();

        foreach (['UseCases', 'Adapters', 'Entities', 'Tests'] as $layer) {
            if (
                !mkdir($concurrentDirectory = $boundedContext->absolutePath()->append($layer)->toString())
                && !is_dir($concurrentDirectory)
            ) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
    }

    private function addEntryForDeptrac(Generator $generator, string $path, string $modulePath): void
    {
        $result = Yaml::parseFile($path);

        if (!\is_array($result)) {
            throw new InvalidArgumentException('Invalid deptrac.yaml file.');
        }

        if (!\in_array($modulePath, $result['imports'] ?? [], true)) {
            $result['imports'][] = $modulePath;
        }
        $generator->dumpFile($path, Yaml::dump($result));
    }
}
