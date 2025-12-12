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

namespace Admin\UseCases\Article\ChangeStorageInformation;

use Admin\Entities\Repository\ArticleRepository;
use Shared\Entities\VO\Packaging;

final readonly class ChangeArticleStorageInformation
{
    public function __construct(private ArticleRepository $articleRepository)
    {
    }

    public function execute(ChangeArticleStorageInformationRequest $request): ChangeArticleStorageInformationResponse
    {
        $article = $this->articleRepository->getByUuid($request->uuid());

        $article->changeStorageInformation(
            Packaging::fromArray($request->packaging()),
            $request->minStock()
        );

        $this->articleRepository->changeStorageInformation($article);

        return new ChangeArticleStorageInformationResponse($article);
    }
}
