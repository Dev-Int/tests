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
            'cancelled' => 'Cancelled',
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
                'counted' => 'Already counted',
                'counted_tooltip' => 'Counted on %date%',
            ],
        ],
        'finish_counting' => [
            'button' => 'Finish counting',
            'success' => 'Counting finished successfully',
            'incomplete_zones' => 'Uncounted items in zones: %zones%',
        ],
        'review' => [
            'titlePage' => 'Inventory review',
            'button' => 'Review',
            'summary' => 'Summary:',
            'discrepancy_count' => '%count% item(s) with discrepancy(ies)',
            'no_discrepancy' => 'No discrepancy detected',
            'article' => 'Article',
            'theoreticalStock' => 'Theoretical stock',
            'realStock' => 'Real stock',
            'difference' => 'Difference',
            'status' => 'Status',
            'select_all' => 'Select all',
            'reviewed' => 'Reviewed',
            'pending' => 'Pending',
            'mark_as_reviewed' => 'Mark as reviewed',
            'no_items_selected' => 'Please select at least one item to review',
            'items_reviewed' => 'Item(s) marked as reviewed successfully',
            'errors' => [
                'no_discrepancy' => 'Cannot review an item that has no stock discrepancy',
                'article_not_found' => 'Article not found in this inventory',
            ],
        ],
        'complete' => [
            'ready' => 'Ready to finalize!',
            'ready_message' => 'All discrepancies have been reviewed. You can now finalize the inventory.',
            'submit' => 'Finalize inventory',
            'success' => 'Inventory finalized successfully. %articles% article(s) updated.',
            'errors' => [
                'not_found' => 'Inventory not found',
                'invalid_status' => 'Inventory must be in review status to be finalized',
                'unreviewed_discrepancies' => '%count% unreviewed discrepancy(ies). Please review them before finalizing.',
            ],
        ],
        'cancel' => [
            'button' => 'Cancel',
            'confirm' => 'Are you sure you want to cancel this inventory?',
            'success' => 'Inventory cancelled successfully',
            'errors' => [
                'cannot_cancel_completed' => 'Cannot cancel a finalized inventory',
            ],
        ],
        'resume_counting' => [
            'button' => 'Resume counting',
            'confirm' => 'Reviews for this zone will be reset. Continue?',
            'success' => 'Counting resumed successfully',
            'errors' => [
                'cannot_resume' => 'Inventory must be in review to resume counting',
            ],
        ],
        'errors' => [
            'not_found' => 'Inventory not found',
            'date_past' => 'The date must be today or in the future',
            'zone_active' => 'An active inventory exists for this zone',
        ],
    ],
];
