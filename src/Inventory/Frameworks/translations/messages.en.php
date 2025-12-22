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
        'errors' => [
            'date_past' => 'The date must be today or in the future',
            'zone_active' => 'An active inventory exists for this zone',
        ],
    ],
];
