<?php

declare(strict_types=1);

return [
    'admin' => [
        'title' => 'Administration',
        'company' => [
            'titlePage' => 'Company',
            'form' => [
                'name' => [
                    'label' => 'Company name',
                    'placeholder' => 'The name of your company',
                ],
                'streetAddress' => [
                    'label' => 'Company streetAddress',
                    'placeholder' => 'The streetAddress of your company',
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
                    'none' => 'No parent',
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
                'success' => 'La zone de stockage est renommée avec succès.',
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
                'streetAddress' => [
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
                'success' => 'The supplier has been renamed successfully.',
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
        'article' => [
            'titlePage' => 'Article',
            'form' => [
                'name' => [
                    'label' => 'Article name',
                ],
                'supplier' => [
                    'label' => 'Supplier',
                    'placeholder' => 'Choice a supplier',
                ],
                'packaging' => [
                    'label' => 'Article packaging',
                ],
                'unitPrice' => [
                    'label' => 'Unit price',
                ],
                'tax' => [
                    'label' => 'Article tax',
                ],
                'minStock' => [
                    'label' => 'Minimum stock',
                ],
                'zoneStorages' => [
                    'label' => 'Stockage areas',
                ],
                'familyLog' => [
                    'label' => 'Logistics family',
                    'placeholder' => 'Choice a logistics family',
                ],
                'quantity' => [
                    'label' => 'Quantity',
                ],
            ],
            'create' => [
                'titlePage' => 'Create new article',
                'titleShort' => 'New article',
                'success' => 'Article created successfully.',
            ],
            'changeFinancialInformation' => [
                'button' => 'Financial information',
                'titlePage' => 'Change financial information of "%articleName%"',
                'titleShort' => 'Update an article',
                'success' => 'Financial information updated successfully.',
            ],
            'changeStorageInformation' => [
                'button' => 'Storage information',
                'titlePage' => 'Change storage information of "%articleName%"',
                'titleShort' => 'Update an article',
                'success' => 'Storage information updated successfully.',
            ],
            'reassignSupplier' => [
                'button' => 'Reassign supplier',
                'titlePage' => 'Reassign supplier of "%articleName%"',
                'titleShort' => 'Reassign "%articleName%"',
                'success' => 'The supplier has been reassigned successfully.',
            ],
            'rename' => [
                'button' => 'Rename',
                'titlePage' => 'Rename the article "%articleName%"',
                'titleShort' => 'Rename "%articleName%"',
                'success' => 'The article has been renamed successfully.',
            ],
            'errors' => [
                'packagingInvalid' => 'parcel should have unit and quantity',
            ],
        ],
        'employee' => [
            'titlePage' => 'Employees',
            'form' => [
                'firstName' => [
                    'label' => 'First Name',
                    'placeholder' => 'Employee first name',
                ],
                'lastName' => [
                    'label' => 'Last Name',
                    'placeholder' => 'Employee last name',
                ],
                'email' => [
                    'label' => 'Email',
                    'placeholder' => 'Employee email',
                ],
                'phone' => [
                    'label' => 'Phone',
                    'placeholder' => 'Employee phone',
                ],
                'position' => [
                    'label' => 'Position',
                    'placeholder' => 'Employee position',
                ],
                'department' => [
                    'label' => 'Department',
                    'placeholder' => 'Employee department',
                ],
                'hiredAt' => [
                    'label' => 'Hired At',
                ],
            ],
            'create' => [
                'titlePage' => 'Create an employee',
                'titleShort' => 'New employee',
                'success' => 'Employee created successfully.',
                'error' => [
                    'userEmailExists' => 'An employee with this email already exists.',
                ],
            ],
            'index' => [
                'titlePage' => 'List of employees',
            ],
        ],
        'homePage' => [
            'titlePage' => 'Home',
            'resume' => '<p>Here is how you administrate your application.</p>
                    <p>You can create, update or archive all parts of the application.</p>
                    <p></p>
                    <p>Happy use <i class="fa-solid fa-face-smile-wink"></i></p>',
        ],
        'configuration' => [
            'titlePage' => 'Configuration',
            'resume' => '<p>Here you configure your application. Select items in order.</p>
                    <p>Each part is important for the proper use of your application.</p>
                    <p></p>
                    <p>Happy configure ;)</p>',
            'application' => [
                'titlePage' => 'Configure application',
                'resume' => '<p>Here you configure specific items of your application. Select items in order.</p>
                            <p>The main units needed to calculate volumes and amounts are defined here.</p>
                            <p></p>
                            <p>Happy configure <i class="fa-solid fa-face-smile-wink"></i></p>',
                'returnButton' => 'Return to configuration',
            ],
        ],
        'returnHome' => 'Return to the home page',
        'adminButton' => 'Return to administration',
    ],
];
