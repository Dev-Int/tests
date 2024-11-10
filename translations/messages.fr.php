<?php

return [
    'actions' => 'Actions',
    'name' => 'Nom',
    'address' => 'Adresse',
    'postalCode' => 'Code postal',
    'phone' => 'Téléphone',
    'email' => 'Adresse email',
    'contact' => 'Contact',
    'label' => 'Intitulé',
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
        'zoneStorage' => [
            'titlePage' => 'Zone de stockage',
            'form' => [
                'label' => [
                    'label' => 'Nom de la zone de stockage',
                    'placeholder' => 'Le nom de la zone de stockage',
                ],
                'familyLog' => [
                    'label' => 'Famille logistique',
                ],
            ],
            'create' => [
                'titlePage' => 'Créer une zone de stockage',
                'titleShort' => 'Nouvelle zone de stockage',
                'success' => 'Zone de stockage créée avec succès.',
            ],
            'changeLabel' => [
                'button' => 'Renommer',
                'titlePage' => 'Renommer la zone de stockage "%zoneLabel%"',
                'titleShort' => 'Renommer "%zoneLabel%"',
                'success' => 'La zone de stockage est renommée avec succès.'
            ],
            'changeFamilyLog' => [
                'button' => 'Changer de famille logistique',
                'titlePage' => 'Changer la famille logistique de "%zoneLabel%"',
                'titleShort' => 'Modifier une zone de stockage',
                'success' => 'Famille logistique assignée avec succès.',
            ],
        ],
        'supplier' => [
            'titlePage' => 'Fournisseur',
            'form' => [
                'name' => [
                    'label' => 'Nom de l\'entreprise',
                ],
                'address' => [
                    'label' => 'Adresse de l\'entreprise',
                ],
                'postalCode' => [
                    'label' => 'Code postal',
                ],
                'city' => [
                    'label' => 'Ville',
                ],
                'country' => [
                    'label' => 'Pays',
                ],
                'phone' => [
                    'label' => 'Téléphone de l\'entreprise',
                ],
                'email' => [
                    'label' => 'Adresse email',
                ],
                'contact' => [
                    'label' => 'Nom du contact',
                ],
                'cellphone' => [
                    'label' => 'Téléphone du contact'
                ],
                'familyLog' => [
                    'label' => 'Famille logistique',
                ],
                'delayDelivery' => [
                    'label' => 'Délai de livraison',
                ],
                'orderDays' => [
                    'label' => 'Jour(s) de commande',
                ],
            ],
            'create' => [
                'titlePage' => 'Créer un fournisseur',
                'titleShort' => 'Nouveau fournisseur',
                'success' => 'Fournisseur créé avec succès.',
            ],
            'rename' => [
                'button' => 'Renommer',
                'titlePage' => 'Renommer le fournisseur "%supplierName%"',
                'titleShort' => 'Renommer "%supplierName%"',
                'success' => 'Le fournisseur est renommé avec succès.'
            ],
            'changeDomiciliation' => [
                'button' => 'Domiciliation',
                'titlePage' => 'Changer la domiciliation de "%supplierName%"',
                'titleShort' => 'Modifier un fournisseur',
                'success' => 'Domiciliation modifiée avec succès.',
            ],
            'changeContact' => [
                'button' => 'Contact',
                'titlePage' => 'Changer le contact de "%supplierName%"',
                'titleShort' => 'Modifier un fournisseur',
                'success' => 'Contact modifié avec succès.',
            ],
            'changeDeliverySpecifications' => [
                'button' => 'Spécifications de livraison',
                'titlePage' => 'Changer les spécifications de livraison de "%supplierName%"',
                'titleShort' => 'Modifier un fournisseur',
                'success' => 'Spécification de livraison modifiées avec succès.',
            ],
        ],
        'article' => [
            'titlePage' => 'Article',
            'form' => [
                'name' => [
                    'label' => 'Nom de l\'article',
                ],
                'supplier' => [
                    'label' => 'Fournisseur',
                    'placeholder' => 'Choisir un fournisseur',
                ],
                'packaging' => [
                    'label' => 'Packaging de l\'article',
                ],
                'unitPrice' => [
                    'label' => 'Prix unitaire',
                ],
                'tax' => [
                    'label' => 'T.V.A. de l\'article',
                    'placeholder' => 'Choisir une taxe',
                ],
                'minStock' => [
                    'label' => 'Stock minimum',
                ],
                'zoneStorages' => [
                    'label' => 'Zones de stockage',
                ],
                'familyLog' => [
                    'label' => 'Famille logistique',
                    'placeholder' => 'Choisir une famille logistique',
                ],
                'quantity' => [
                    'label' => 'Quantité',
                ],
            ],
            'create' => [
                'titlePage' => 'Créer un article',
                'titleShort' => 'Nouvel article',
                'success' => 'Article créé avec succès.',
            ],
            'changeFinancialInformation' => [
                'button' => 'Informations financières',
                'titlePage' => 'Changer les informations financières de "%articleName%"',
                'titleShort' => 'Modifier un article',
                'success' => 'Informations financières modifiées avec succès.',
            ],
            'changeStorageInformation' => [
                'button' => 'Information de stockage',
                'titlePage' => 'Changer les informations de stockage de "%articleName%"',
                'titleShort' => 'Modifier un article',
                'success' => 'Informations de stockage modifiées avec succès.',
            ],
            'reassignSupplier' => [
                'button' => 'Réassigner un fournisseur',
                'titlePage' => 'Réassigner un fournisseur à "%articleName%"',
                'titleShort' => 'Réassigner "%articleName%"',
                'success' => 'Le fournisseur a été réassigné avec succès.'
            ],
            'rename' => [
                'button' => 'Renommer',
                'titlePage' => 'Renommer le fournisseur "%articleName%"',
                'titleShort' => 'Renommer "%articleName%"',
                'success' => 'Le fournisseur est renommé avec succès.'
            ],
        ],
    ],
];
