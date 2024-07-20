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

namespace Admin\UseCases\Article\ChangeFinancialInformation;

use Admin\UseCases\Gateway\ArticleRepository;
use Shared\Entities\VO\Amount;

final readonly class ChangeArticleFinancialInformation
{
    public function __construct(private ArticleRepository $articleRepository)
    {
    }

    public function execute(ChangeArticleFinancialInformationRequest $request): ChangeArticleFinancialInformationResponse
    {
        $article = $this->articleRepository->findByUuid($request->uuid());

        $article->changeFinancialInformation(Amount::fromInt($request->amount()), $request->tax());

        $this->articleRepository->changeFinancialInformation($article);

        return new ChangeArticleFinancialInformationResponse($article);
    }
}
