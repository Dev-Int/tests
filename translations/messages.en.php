<?php

return [
    'actions' => 'Actions',
    'name' => 'Name',
    'address' => 'Address',
    'phone' => 'Phone',
    'email' => 'Email',
    'contact' => 'Contact',
    'admin' => [
        'company' => [
            'titlePage' => 'Company',
            'form' => [
                'name' => [
                    'label' => 'Company name',
                    'placeholder' => 'The name of your company',
                ],
                'address' => [
                    'label' => 'Company address',
                    'placeholder' => 'The address of your company',
                ],
                'postalCode' => [
                    'label' => 'postal code',
                    'placeholder' => 'Postal code where your company is domiciled',
                ],
                'city' => [
                    'label' => 'City',
                    'placeholder' => 'City where your company is domiciled',
                ],
                'country' => [
                    'label' => 'Country',
                    'placeholder' => 'Country where your company is domiciled',
                ],
                'phone' => [
                    'label' => 'Phone',
                    'placeholder' => 'The phone number of your company',
                ],
                'email' => [
                    'label' => 'Email',
                    'placeholder' => 'Email of your company',
                ],
                'contact' => [
                    'label' => 'Contact of your company',
                    'placeholder' => 'The name of the contact person of your company',
                ],
            ],
            'create' => [
                'titlePage' => 'Create Company',
                'success' => 'Company created successfully.',
            ],
            'update' => [
                'titlePage' => 'Update Company "%companyName%"',
                'titleShort' => 'Update "%companyName%"',
                'success' => 'Company updated successfully.',
            ],
        ],
        'unit' => [
            'titlePage' => 'Units',
            'form' => [
                'label' => [
                    'label' => 'Unit label',
                    'placeholder' => 'The label of the unit',
                ],
                'abbreviation' => [
                    'label' => 'Unit Abbreviation',
                    'placeholder' => 'The abbreviation of the unit',
                ],
            ],
            'create' => [
                'titlePage' => 'Create unit',
                'titleShort' => 'New unit',
                'success' => 'Unit created successfully.',
            ],
            'changeLabel' => [
                'button' => 'Change label',
                'titlePage' => 'Change label "%unitName%"',
                'titleShort' => 'Rename "%unitName%"',
                'success' => 'Unit label changed successfully.'
            ],
        ],
        'tax' => [
            'titlePage' => 'Tax',
            'form' => [
                'name' => [
                    'label' => 'Tax name',
                ],
                'rate' => [
                    'label' => 'Tax rate',
                ],
            ],
            'create' => [
                'titlePage' => 'Create tax',
                'titleShort' => 'New taxe',
                'success' => 'Taxe created successfully.',
            ],
            'rename' => [
                'button' => 'Rename',
                'titlePage' => 'Rename tax "%taxName%"',
                'titleShort' => 'Rename "%taxName%"',
                'success' => 'Tax renamed successfully.'
            ],
            'revaluate' => [
                'button' => 'Revaluate',
                'titlePage' => 'Revaluate tax "%taxName%"',
                'titleShort' => 'Revaluate "%taxName%"',
                'success' => 'Tax revaluated successfully.'
            ],
        ],
    ],
];
