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

namespace Shared\Adapters\Controller\Symfony\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ErrorController extends AbstractController
{
    public function __invoke(\Throwable $exception): Response
    {
        $flattenException = FlattenException::createFromThrowable($exception);
        $statusCode = $flattenException->getStatusCode();

        $this->addFlash('error', $flattenException->getMessage());

        return $this->render(sprintf('bundles/TwigBundle/Exception/error%d.html.twig', $statusCode), [
            'message' => $flattenException->getStatusText(),
        ]);
    }
}
