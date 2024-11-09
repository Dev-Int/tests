<?php

return [
    'actions' => 'Actions',
    'name' => 'Nom',
    'address' => 'Adresse',
    'phone' => 'Téléphone',
    'email' => 'Adresse email',
    'contact' => 'Contact',
    'rate' => 'Taux',
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
                'titleShort' => 'Nouvelle compagnie',
                'success' => 'Compagnie créée avec succès.',
            ],
            'update' => [
                'titlePage' => 'Modification de la compagnie "%companyName%"',
                'titleShort' => 'Modifier "%companyName%"',
                'success' => 'Compagnie modifiée avec succès.'
            ]
        ],
        'unit' => [
            'titlePage' => 'Unités',
            'form' => [
                'label' => [
                    'label' => 'Intitulé',
                    'placeholder' => 'Intitulé de l\'unité',
                ],
                'abbreviation' => [
                    'label' => 'Abréviation',
                    'placeholder' => 'Abréviation de l\'unité',
                ],
            ],
            'create' => [
                'titlePage' => 'Création d\'une unité',
                'titleShort' => 'Nouvelle unité',
                'success' => 'Unité créée avec succès.',
            ],
            'changeLabel' => [
                'button' => 'Renommer',
                'titlePage' => 'Changer l\'intitulé de l\'unité "%unitName%"',
                'titleShort' => 'Renommer "%unitName%"',
                'success' => 'L\'intitulé de l\'unité changé avec succès.'
            ],
        ],
        'tax' => [
            'titlePage' => 'Taxe',
            'form' => [
                'name' => [
                    'label' => 'Nom de la taxe',
                ],
                'rate' => [
                    'label' => 'Taux de la taxe',
                ],
            ],
            'create' => [
                'titlePage' => 'Création d\'une taxe',
                'titleShort' => 'Nouvelle taxe',
                'success' => 'Taxe créée avec succès.',
            ],
            'rename' => [
                'button' => 'Renommer',
                'titlePage' => 'Renommer la taxe "%taxName%"',
                'titleShort' => 'Renommer "%taxName%"',
                'success' => 'La taxe est renommée avec succès.'
            ],
            'revaluate' => [
                'button' => 'Réévaluer',
                'titlePage' => 'Réévaluer la taxe "%taxName%"',
                'titleShort' => 'Réévaluer "%taxName%"',
                'success' => 'La taxe est réévaluée avec succès.'
            ],
        ],
        'familyLog' => [
            'titlePage' => 'Famille logistique',
            'form' => [
                'name' => [
                    'label' => 'Nom de la famille logistique',
                    'placeholder' => 'Le nom de la famille logistique',
                ],
                'parent' => [
                    'label' => 'Famille logistique parente',
                ],
            ],
            'create' => [
                'titlePage' => 'Création d\'une famille logistique',
                'titleShort' => 'Nouvelle famille logistique',
                'success' => 'Famille logistique créée avec succès.',
            ],
            'changeLabel' => [
                'button' => 'Renommer',
                'titlePage' => 'Renommer la famille logistique "%familyLabel%"',
                'titleShort' => 'Renommer "%familyLabel%"',
                'success' => 'La famille logistique est renommée avec succès.'
            ],
            'assignParent' => [
                'button' => 'Assigner un parent',
                'titlePage' => 'Assigner un parent à "%familyLabel%"',
                'titleShort' => 'Modifier une famille logistique',
                'success' => 'Famille logistique parente assignée avec succès.',
            ],
        ],
    ],
];
