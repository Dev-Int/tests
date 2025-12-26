<?php

declare(strict_types=1);

return [
    'inventory' => [
        'titlePage' => 'Inventory',
        'form' => [
            'date' => [
                'label' => 'Inventory date',
                'placeholder' => 'Choose inventory date',
            ],
            'zoneStorages' => [
                'label' => 'Storage area',
            ],
        ],
        'status' => [
            'label' => 'Status',
            'draft' => 'Draft',
            'inProgress' => 'In progress',
            'review' => 'In review',
            'completed' => 'Completed',
        ],
        'create' => [
            'titlePage' => 'Create an inventory',
            'titleShort' => 'New inventory',
            'success' => 'Inventory created successfully',
        ],
        'start' => [
            'success' => 'Inventory started successfully',
            'button' => 'Start',
        ],
        'zone' => [
            'record' => [
                'titlePage' => 'Record real stock',
                'no_articles' => 'No articles in this zone',
                'button' => 'Record stock',
                'submit' => 'Save',
                'cancel' => 'Cancel',
                'success' => 'Real stock recorded successfully',
                'article' => 'Article',
                'theoreticalStock' => 'Theoretical stock',
                'realStock' => 'Real stock',
            ],
        ],
        'cancel' => 'Cancel',
        'errors' => [
            'date_past' => 'The date must be today or in the future',
            'zone_active' => 'An active inventory exists for this zone',
        ],
    ],
];
