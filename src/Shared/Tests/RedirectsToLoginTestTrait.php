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

namespace Shared\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trait pour tester la redirection vers login pour les routes protégées.
 *
 * Utiliser ce trait évite de dupliquer le même test dans chaque contrôleur.
 * La classe de test doit implémenter getProtectedUri() pour définir l'URI à tester.
 *
 * Dépendances :
 * - logoutUser() : fourni par AuthenticatedFunctionalTestTrait
 * - getHttpClient() : fourni par BaseFunctionalTestCase
 */
trait RedirectsToLoginTestTrait
{
    public function testRedirectsToLoginWhenUnauthenticated(): void
    {
        $this->logoutUser();
        $this->getHttpClient()->request($this->getProtectedHttpMethod(), $this->getProtectedUri());
        self::assertResponseRedirects('/login');
    }

    /**
     * Retourne l'URI protégée à tester.
     */
    abstract protected function getProtectedUri(): string;

    /**
     * Retourne la méthode HTTP à utiliser pour tester l'URI protégée.
     * Par défaut GET, à surcharger si la route requiert POST, etc.
     */
    protected function getProtectedHttpMethod(): string
    {
        return Request::METHOD_GET;
    }

    /**
     * Déconnecte l'utilisateur actuel.
     * Fourni par AuthenticatedFunctionalTestTrait via BaseFunctionalTestCase.
     */
    abstract protected function logoutUser(): void;

    /**
     * Retourne le client HTTP pour les requêtes.
     * Fourni par BaseFunctionalTestCase.
     */
    abstract protected function getHttpClient(): KernelBrowser;
}
