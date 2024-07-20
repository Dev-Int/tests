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

namespace Admin\UseCases\Article\RenameArticle;

use Admin\Entities\Exception\ArticleAlreadyExistsException;
use Admin\UseCases\Gateway\ArticleRepository;
use Shared\Entities\VO\NameField;

final readonly class RenameArticle
{
    public function __construct(private ArticleRepository $articleRepository)
    {
    }

    public function execute(RenameArticleRequest $request): RenameArticleResponse
    {
        $isExists = $this->articleRepository->isExists($request->name());
        if ($isExists) {
            throw new ArticleAlreadyExistsException($request->name());
        }

        $article = $this->articleRepository->findByUuid($request->uuid());

        $article->rename(NameField::fromString($request->name()));

        $this->articleRepository->renameArticle($article);

        return new RenameArticleResponse($article);
    }
}
