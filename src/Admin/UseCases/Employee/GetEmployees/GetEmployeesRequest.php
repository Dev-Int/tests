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

namespace Admin\UseCases\Employee\GetEmployees;

interface GetEmployeesRequest
{
    // @todo: Développement futur - Ajouter des filtres optionnels :
    // - status: Filtrer par EmployeeStatus (ACTIVE/INACTIVE)
    // - department: Filtrer par nom de département
    // - search: Recherche dans firstName, lastName, email
    // - sortBy: Ordre de tri (name, email, hiredAt, etc.)
}
