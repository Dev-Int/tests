<?php

return [
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
                'titleShort' => 'Modify "%companyName%"',
                'success' => 'Company updated successfully.',
            ],
        ],
    ],
];
