<?php

return [
    'connection' => 'Connexion',
    'actions' => 'Actions',
    'name' => 'Nom',
    'address' => 'Adresse',
    'postalCode' => 'Code postal',
    'phone' => 'Téléphone',
    'email' => 'Adresse email',
    'contact' => 'Contact',
    'label' => 'Intitulé',
    'rate' => 'Taux',
    'paginate' => 'Pagination',
    'admin' => [
        'titlePage' => 'Administration',
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
            'errors' => [
                'packagingInvalid' => 'Le colis doit avoir une unité et une quantité',
            ],
        ],
        'homePage' => [
            'titlePage' => 'Home',
            'resume' => '<p>C\'est ici que vous administrez votre application.</p>
                    <p>Vous pouvez créer, mettre à jour ou archiver toutes les parties de l\'application.</p>
                    <p></p>
                    <p>Bonne utilisation <i class="fa-solid fa-face-smile-wink"></i></p>',
        ],
        'configuration' => [
            'titlePage' => 'Configuration',
            'resume' => '<p>C\'est ici que vous configurez votre application. Sélectionnez les éléments dans l\'ordre.</p>
                    <p>Chaque partie est importante pour l\'utilisation correcte de votre application.</p>
                    <p></p>
                    <p>Bonne configuration <i class="fa-solid fa-face-smile-wink"></i></p>',
            'application' => [
                'titlePage' => 'Configurer l\'application',
                'resume' => '<p>Vous configurez ici des éléments spécifiques de votre application. Sélectionnez les éléments dans l\'ordre.</p>
                            <p>Les principales unités nécessaires au calcul des volumes et des quantités sont définies ici.</p>
                            <p></p>
                            <p>Bonne configuration <i class="fa-solid fa-face-smile-wink"></i></p>',
                'returnButton' => 'Retour à la configuration'
            ],
        ],
        'returnButton' => 'Retour à l\'accueil',
        'adminButton' => 'Retour à l\'administration',
    ],
    'home' => [
        'titlePage' => 'Accueil',
        'welcome' => 'Bienvenue dans l\'application de gestion des stocks de votre restaurant !',
        'text' => [
            'intro' => 'Une application de gestion des stocks d\'un restaurant est comme un garde-manger
                        numérique et un grand livre combinés. spécialement conçue pour rationaliser le processus
                        de suivi, de commande et de gestion des niveaux de stock pour les ingrédients, les fournitures
                        et l\'équipement dans un restaurant, des ingrédients, des fournitures et de l\'équipement
                        dans un restaurant. Voici un aperçu de ses fonctionnalités :',
            '1' => [
                'title' => '1. Suivi des stocks :',
                'text' => 'La principale caractéristique de l\'application est de conserver un enregistrement en temps
                           réel de tous les articles en stock. Elle permet au personnel du restaurant de contrôler
                           facilement les quantités, les dates d\'expiration et les modes d\'utilisation des
                           ingrédients et des fournitures.',
            ],
            '2' => [
                'title' => '2. Commande et achat :',
                'text' => 'L\'application facilite le processus de commande en générant des bons de commande
                           automatiques lorsque les niveaux de stock tombent en dessous d\'un certain seuil.
                           Elle peut également s\'intégrer aux systèmes des fournisseurs pour rationaliser le
                           processus d\'achat.',
            ],
            '3' => [
                'title' => '3. Gestion des recettes :',
                'text' => 'Il permet aux chefs et au personnel de cuisine de créer et de stocker des recettes dans
                           l\'application, en spécifiant les ingrédients nécessaires pour chaque plat. ingrédients
                           requis pour chaque plat. Cette fonction permet de suivre avec précision l\'utilisation des
                           ingrédients et de calculer le coût de chaque recette.'
            ],
            '4' => [
                'title' => '4. Prévisions et analyses :',
                'text' => 'En analysant les données historiques et les tendances, l\'application peut fournir des
                           informations sur les modèles d\'utilisation des stocks. de l\'utilisation des stocks,
                           aidant ainsi les propriétaires et les gérants de restaurants à prendre des décisions
                           éclairées sur les niveaux de stock, la planification des menus et les stratégies de
                           tarification.',
            ],
            '5' => [
                'title' => '5. Gestion des fournisseurs :',
                'text' => 'Il centralise les informations sur les fournisseurs, y compris les coordonnées, les accords
                           tarifaires, les calendriers de livraison et l\'historique des commandes, ce qui facilite la
                           gestion des relations et la négociation de conditions favorables.',
            ],
            '6' => [
                'title' => '6. Accessibilité mobile :',
                'text' => 'De nombreuses applications de gestion des stocks offrent une compatibilité mobile,
                           permettant au personnel d\'accéder aux données d\'inventaire, de passer des commandes et de
                           mettre à jour les niveaux de stock depuis n\'importe quel endroit, ce qui améliore la
                           flexibilité et l\'efficacité.',
            ],
            '7' => [
                'title' => '7. Intégration avec les systèmes POS :',
                'text' => 'L\'intégration avec le système de point de vente (POS) du restaurant garantit une
                           communication transparente entre la gestion des stocks et les données de vente, offrant
                           ainsi une vue d\'ensemble. entre la gestion des stocks et les données relatives aux ventes,
                           ce qui permet d\'avoir une vue d\'ensemble de la santé financière de l\'entreprise. de la
                           santé financière de l\'entreprise.',
            ],
            '8' => [
                'title' => '8. Alertes et notifications :',
                'text' => 'L\'application peut envoyer des alertes et des notifications aux utilisateurs lorsque les
                           niveaux de stock sont bas, lorsque les articles sont sur le point d\'expirer ou lorsque de
                           nouvelles commandes ont été passées ou reçues, ce qui permet d\'agir en temps voulu et de
                           minimiser le gaspillage.',
            ],
            'conclusion' => 'Dans l\'ensemble, une application de gestion des stocks d\'un restaurant est un outil
                             essentiel pour optimiser les opérations, réduire les coûts et veiller à ce que la cuisine
                             soit bien approvisionnée avec les ingrédients nécessaires pour répondre à la demande des
                             clients tout en minimisant les déchets.',
        ],
    ],
];
