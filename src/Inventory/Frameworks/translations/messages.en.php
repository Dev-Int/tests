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
                'confirm_zero' => 'Warning: the following articles will have zero stock. Do you want to continue?',
                'info' => 'Please enter the real stock for each article. Enter 0 if the article is out of stock.',
                'error_missing_fields' => 'Please enter the real stock for the following articles: %articles%',
            ],
        ],
        'finish_counting' => [
            'button' => 'Finish counting',
            'success' => 'Counting finished successfully',
            'incomplete_zones' => 'Uncounted items in zones: %zones%',
        ],
        'review' => [
            'titlePage' => 'Inventory review',
            'summary' => 'Summary:',
            'discrepancy_count' => '%count% item(s) with discrepancy(ies)',
            'no_discrepancy' => 'No discrepancy detected',
            'article' => 'Article',
            'theoreticalStock' => 'Theoretical stock',
            'realStock' => 'Real stock',
            'difference' => 'Difference',
        ],
        'cancel' => 'Cancel',
        'errors' => [
            'date_past' => 'The date must be today or in the future',
            'zone_active' => 'An active inventory exists for this zone',
        ],
    ],
];
