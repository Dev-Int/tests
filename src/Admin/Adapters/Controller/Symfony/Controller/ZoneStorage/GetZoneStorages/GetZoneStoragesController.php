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

namespace Admin\Adapters\Controller\Symfony\Controller\ZoneStorage\GetZoneStorages;

use Admin\Entities\Exception\NoZoneStorageRegisteredException;
use Admin\UseCases\ZoneStorage\GetZoneStorages\GetZoneStorages;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class GetZoneStoragesController extends AbstractController
{
    public function __construct(private readonly GetZoneStorages $useCase)
    {
    }

    #[Route(path: '/zone_storages', name: 'admin_zone_storages_index')]
    public function __invoke(): Response
    {
        try {
            $zoneStorages = $this->useCase->execute();
        } catch (NoZoneStorageRegisteredException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('admin_configure');
        }

        $response = new GetZoneStoragesWebResponse($zoneStorages);

        return $this->render('@admin/zoneStorages/index.html.twig', [
            'zoneStorages' => $response->zoneStorages(),
        ]);
    }
}
