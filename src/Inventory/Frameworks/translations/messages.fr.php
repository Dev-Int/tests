<?php

declare(strict_types=1);

return [
    'inventory' => [
        'titlePage' => 'Inventaire',
        'form' => [
            'date' => [
                'label' => 'Date de d\'inventaire',
                'placeholder' => 'Choisissez la date de l\'inventaire',
            ],
            'zoneStorages' => [
                'label' => 'Zone de stockage',
            ],
        ],
        'status' => [
            'label' => 'Statut',
            'draft' => 'Brouillon',
            'inProgress' => 'En cours',
            'review' => 'En révision',
            'completed' => 'Terminé',
        ],
        'create' => [
            'titlePage' => 'Créer un inventaire',
            'titleShort' => 'Nouvel inventaire',
            'success' => 'Inventaire créé avec succès',
        ],
        'errors' => [
            'date_past' => 'La date doit être aujourd\'hui ou dans le futur',
            'zone_active' => 'Un inventaire actif existe pour cette zone',
        ],
    ],
];
