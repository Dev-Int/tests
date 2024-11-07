<?php

return [
    'name' => 'Nom',
    'address' => 'Adresse',
    'phone' => 'Téléphone',
    'email' => 'Adresse email',
    'contact' => 'Contact',
    'admin' => [
        'company' => [
            'titlePage' => 'Compagnie',
            'form' => [
                'name' => [
                    'label' => 'Nom de la société',
                    'placeholder' => 'Le nom de votre société',
                ],
                'address' => [
                    'label' => 'Adresse de la société',
                    'placeholder' => 'L\'adresse de votre société',
                ],
                'postalCode' => [
                    'label' => 'Code postal',
                    'placeholder' => 'Le code postal où est domiciliée votre société',
                ],
                'city' => [
                    'label' => 'Ville',
                    'placeholder' => 'La ville où est domiciliée votre société',
                ],
                'country' => [
                    'label' => 'Pays',
                    'placeholder' => 'Le pays où est domiciliée votre société',
                ],
                'phone' => [
                    'label' => 'Téléphone',
                    'placeholder' => 'Le téléphone de votre société',
                ],
                'email' => [
                    'label' => 'Adresse email',
                    'placeholder' => 'L\'adresse mail de votre société',
                ],
                'contact' => [
                    'label' => 'Contact de la société',
                    'placeholder' => 'Le nom du contact de votre société',
                ],
            ],
            'create' => [
                'titlePage' => 'Création d\'une compagnie',
                'success' => 'Compagnie créée avec succès.',
            ],
            'update' => [
                'titlePage' => 'Modification de la compagnie "%companyName%"',
                'titleShort' => 'Modifier "%companyName%"',
                'success' => 'Compagnie mise à jour avec succès.'
            ]
        ],
    ],
];
