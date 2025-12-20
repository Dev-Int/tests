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

namespace Shared\Adapters\Symfony\Maker\Model;

use Symfony\Bundle\MakerBundle\Str;

final readonly class BoundedContext
{
    public static function new(
        string $name,
        string $rootNamespace,
        string $rootPath,
        string $sourcePrefix = 'src'
    ): self {
        $name = Str::asClassName($name);

        return new self(
            $name,
            NamespaceValue::fromString($rootNamespace)->append($name),
            Path::fromString($rootPath)->append($sourcePrefix, $name),
            Path::fromString($sourcePrefix)->append($name)
        );
    }

    private function __construct(
        private string $name,
        private NamespaceValue $namespace,
        private Path $absolutePath,
        private Path $relativePath
    ) {
    }

    public function createUseCase(string $useCaseName): UseCase
    {
        $useCaseName = Str::asClassName($useCaseName);

        $requestClassName = Str::asClassName($useCaseName, 'Request');
        $responseClassName = Str::asClassName($useCaseName, 'Response');

        return new UseCase(
            $useCaseName,
            $this
                ->namespace()
                ->append('UseCases')
                ->append($useCaseName),
            $this
                ->namespace()
                ->append('Tests')
                ->append('UseCases')
                ->append($useCaseName),
            $this
                ->absolutePath()
                ->append('UseCases', $useCaseName),
            $requestClassName,
            $responseClassName,
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function namespace(): NamespaceValue
    {
        return $this->namespace;
    }

    public function absolutePath(): Path
    {
        return $this->absolutePath;
    }

    public function relativePath(): Path
    {
        return $this->relativePath;
    }
}
