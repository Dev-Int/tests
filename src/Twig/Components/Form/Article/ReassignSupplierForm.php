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

namespace App\Twig\Components\Form\Article;

use Admin\Adapters\Controller\Symfony\Controller\Article\ReAssignArticleSupplier\ReAssignArticleSupplierApiRequest;
use Admin\Adapters\Form\Type\Article\ReAssignSupplierType;
use Admin\Adapters\Gateway\ORM\Entity\Article\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ReassignSupplierForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ReAssignArticleSupplierApiRequest $initialFormData;

    #[LiveProp]
    public Article $article;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ReAssignSupplierType::class, $this->initialFormData);
    }
}
