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

use Symfony\Component\Panther\Client;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Trait pour authentifier un utilisateur dans les tests E2E Panther.
 *
 * Utilise l'utilisateur créé par UserFixtures: admin@tests.local / password.
 * Le login se fait via le formulaire réel (contrairement aux tests fonctionnels qui utilisent loginUser()).
 */
trait AuthenticatedPantherTestTrait
{
    /**
     * Authentifie l'utilisateur via le formulaire de login.
     */
    protected function loginViaForm(Client $client, TranslatorInterface $translator): void
    {
        $client->request('GET', '/login');

        $client->submitForm($translator->trans('auth.login.form.submit'), [
            '_email' => 'admin@tests.local',
            '_password' => 'password',
        ]);

        // Attendre la redirection vers home (élément spécifique à la page destination)
        // Timeout explicite pour éviter les faux positifs si la structure de page change
        $client->waitForElementToContain('h1', $translator->trans('home.welcome'), 10);
    }
}
