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

use Admin\Entities\Article\VO\Packaging;
use Admin\Entities\Repository\ArticleRepository;

final readonly class ChangeArticleStorageInformation
{
    public function __construct(private ArticleRepository $articleRepository)
    {
    }

    public function execute(ChangeArticleStorageInformationRequest $request): ChangeArticleStorageInformationResponse
    {
        $article = $this->articleRepository->getByUuid($request->uuid());

        $packages = $request->packaging();
        $packaging = new Packaging($packages[0], $packages[1], $packages[2]);

        $article->changeStorageInformation(
            $packaging,
            $request->minStock()
        );

        $this->articleRepository->changeStorageInformation($article);

        return new ChangeArticleStorageInformationResponse($article);
    }
}
