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

final readonly class UseCase
{
    public const TEMPLATE_FILENAME = __DIR__ . '/../Templates/useCaseClass.tpl.php';
    public const REQUEST_TEMPLATE_FILENAME = __DIR__ . '/../Templates/useCaseRequest.tpl.php';
    public const RESPONSE_TEMPLATE_FILENAME = __DIR__ . '/../Templates/useCaseResponse.tpl.php';
    public const TEST_TEMPLATE_FILENAME = __DIR__ . '/../Templates/useCaseTest.tpl.php';

    public function __construct(
        private string $name,
        private NamespaceValue $namespace,
        private NamespaceValue $testNamespace,
        private Path $absolutePath,
        private string $requestClassName,
        private string $responseClassName,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function filename(): string
    {
        return $this->name . '.php';
    }

    public function absoluteFilenamePath(): string
    {
        return $this->absolutePath->append($this->name . '.php')->toString();
    }

    public function namespace(): NamespaceValue
    {
        return $this->namespace;
    }

    public function testNamespace(): NamespaceValue
    {
        return $this->testNamespace;
    }

    public function absoluteTestClassFilenamePath(): string
    {
        return $this->absolutePath->append(
            '..',
            '..',
            'Tests',
            'UseCases',
            $this->name,
            $this->name . 'Test.php'
        )->toString();
    }

    public function requestClassName(): string
    {
        return $this->requestClassName;
    }

    public function requestClassFilename(): string
    {
        return $this->requestClassName . '.php';
    }

    public function absoluteRequestClassFilenamePath(): string
    {
        return $this->absolutePath->append($this->requestClassName . '.php')->toString();
    }

    public function responseClassName(): string
    {
        return $this->responseClassName;
    }

    public function responseClassFilename(): string
    {
        return $this->responseClassName . '.php';
    }

    public function absoluteResponseClassFilenamePath(): string
    {
        return $this->absolutePath->append($this->responseClassName . '.php')->toString();
    }
}
