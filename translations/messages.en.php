<?php

return [
    'actions' => 'Actions',
    'name' => 'Name',
    'address' => 'Address',
    'postalCode' => 'Postal code',
    'phone' => 'Phone',
    'email' => 'Email',
    'contact' => 'Contact',
    'label' => 'Label',
    'rate' => 'Rate',
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
                'success' => 'Unit label changed successfully.',
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
                'titleShort' => 'New tax',
                'success' => 'Taxe created successfully.',
            ],
            'rename' => [
                'button' => 'Rename',
                'titlePage' => 'Rename tax "%taxName%"',
                'titleShort' => 'Rename "%taxName%"',
                'success' => 'Tax renamed successfully.',
            ],
            'revaluate' => [
                'button' => 'Revaluate',
                'titlePage' => 'Revaluate tax "%taxName%"',
                'titleShort' => 'Revaluate "%taxName%"',
                'success' => 'Tax revaluated successfully.',
            ],
        ],
        'familyLog' => [
            'titlePage' => 'Logistics family',
            'form' => [
                'name' => [
                    'label' => 'Logistics family name',
                    'placeholder' => 'The name of the logistics family',
                ],
                'parent' => [
                    'label' => 'Related logistics family',
                ],
            ],
            'create' => [
                'titlePage' => 'Create logistics family',
                'titleShort' => 'New logistics family',
                'success' => 'Logistics family created successfully.',
            ],
            'changeLabel' => [
                'button' => 'Change label',
                'titlePage' => 'Change label to "%familyLabel%"',
                'titleShort' => 'Update logistique family',
                'success' => 'Logistics family renamed successfully.',
            ],
            'assignParent' => [
                'button' => 'Assign parent',
                'titlePage' => 'Assign parent to "%familyLabel%"',
                'titleShort' => 'Update logistique family',
                'success' => 'FamilyLog parent assigned successfully.',
            ],
        ],
        'zoneStorage' => [
            'titlePage' => 'Storage area',
            'form' => [
                'name' => [
                    'label' => 'Name of the storage area',
                    'placeholder' => 'The name of the storage area',
                ],
                'parent' => [
                    'label' => 'Famille logistique parente',
                ],
            ],
            'create' => [
                'titlePage' => 'Create new storage area',
                'titleShort' => 'New storage area',
                'success' => 'Storage area created successfully.',
            ],
            'changeLabel' => [
                'button' => 'Change label',
                'titlePage' => 'Change label of the storage area "%zoneLabel%"',
                'titleShort' => 'Rename "%zoneLabel%"',
                'success' => 'La zone de stockage est renommée avec succès.'
            ],
            'changeFamilyLog' => [
                'button' => 'Change logistics family',
                'titlePage' => 'Change logistics family to "%zoneLabel%"',
                'titleShort' => 'Update storage area',
                'success' => 'Logistics family assigned successfully.',
            ],
        ],
        'supplier' => [
            'titlePage' => 'Supplier',
            'form' => [
                'name' => [
                    'label' => 'Name of the supplier',
                ],
                'address' => [
                    'label' => 'Address of the supplier',
                ],
                'postalCode' => [
                    'label' => 'postal code',
                ],
                'city' => [
                    'label' => 'City',
                ],
                'country' => [
                    'label' => 'Country',
                ],
                'phone' => [
                    'label' => 'Phone number',
                ],
                'email' => [
                    'label' => 'Email',
                ],
                'contact' => [
                    'label' => 'Name of the contact',
                ],
                'cellphone' => [
                    'label' => 'Phone number of the contact',
                ],
                'familyLog' => [
                    'label' => 'Logistics family',
                ],
                'delayDelivery' => [
                    'label' => 'Delay of delivery',
                ],
                'orderDays' => [
                    'label' => 'Order days',
                ],
            ],
            'create' => [
                'titlePage' => 'Create new supplier',
                'titleShort' => 'New supplier',
                'success' => 'Supplier created successfully.',
            ],
            'rename' => [
                'button' => 'Rename',
                'titlePage' => 'Rename the supplier "%supplierName%"',
                'titleShort' => 'Rename "%supplierName%"',
                'success' => 'The supplier has been renamed successfully.'
            ],
            'changeDomiciliation' => [
                'button' => 'Domiciliation',
                'titlePage' => 'Change domiciliation of "%supplierName%"',
                'titleShort' => 'Update supplier',
                'success' => 'Domiciliation updated successfully.',
            ],
            'changeContact' => [
                'button' => 'Contact',
                'titlePage' => 'Change the contact of "%supplierName%"',
                'titleShort' => 'Update supplier',
                'success' => 'Contact updated successfully.',
            ],
            'changeDeliverySpecifications' => [
                'button' => 'Specifications of delivery',
                'titlePage' => 'Change specification of delivery "%supplierName%"',
                'titleShort' => 'Update supplier',
                'success' => 'Specification of delivery updated successfully.',
            ],
        ],
    ],
];
