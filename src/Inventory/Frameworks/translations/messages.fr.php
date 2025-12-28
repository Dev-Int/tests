<?php

declare(strict_types=1);

return [
    'inventory' => [
        'titlePage' => 'Inventaire',
        'form' => [
            'date' => [
                'label' => 'Date de d\'inventaire',
                'placeholder' => 'Choisissez la date de l\'inventaire',
            ],
            'zoneStorages' => [
                'label' => 'Zone de stockage',
            ],
        ],
        'status' => [
            'label' => 'Statut',
            'draft' => 'Brouillon',
            'inProgress' => 'En cours',
            'review' => 'En révision',
            'completed' => 'Terminé',
        ],
        'create' => [
            'titlePage' => 'Créer un inventaire',
            'titleShort' => 'Nouvel inventaire',
            'success' => 'Inventaire créé avec succès',
        ],
        'start' => [
            'success' => 'Inventaire démarré avec succès',
            'button' => 'Démarrer',
        ],
        'zone' => [
            'record' => [
                'titlePage' => 'Saisie du stock réel',
                'no_articles' => 'Aucun article dans cette zone',
                'button' => 'Saisir stock',
                'submit' => 'Enregistrer',
                'cancel' => 'Annuler',
                'success' => 'Stock réel enregistré avec succès',
                'article' => 'Article',
                'theoreticalStock' => 'Stock théorique',
                'realStock' => 'Stock réel',
                'confirm_zero' => 'Attention : les articles suivants auront un stock à 0. Voulez-vous continuer ?',
                'info' => 'Veuillez saisir le stock réel pour chaque article. Entrez 0 si l\'article n\'est plus en stock.',
                'error_missing_fields' => 'Veuillez renseigner le stock réel pour les articles suivants : %articles%',
            ],
        ],
        'finish_counting' => [
            'button' => 'Terminer le comptage',
            'success' => 'Comptage terminé avec succès',
            'incomplete_zones' => 'Articles non comptés dans les zones : %zones%',
        ],
        'review' => [
            'titlePage' => 'Revue de l\'inventaire',
            'summary' => 'Résumé :',
            'discrepancy_count' => '%count% article(s) avec écart(s)',
            'no_discrepancy' => 'Aucun écart détecté',
            'article' => 'Article',
            'theoreticalStock' => 'Stock théorique',
            'realStock' => 'Stock réel',
            'difference' => 'Écart',
            'status' => 'Statut',
            'select_all' => 'Tout sélectionner',
            'reviewed' => 'Révisé',
            'pending' => 'En attente',
            'mark_as_reviewed' => 'Marquer comme révisé',
            'no_items_selected' => 'Veuillez sélectionner au moins un article à réviser',
            'items_reviewed' => 'Article(s) marqué(s) comme révisé(s) avec succès',
            'errors' => [
                'no_discrepancy' => 'Impossible de réviser un article sans écart de stock',
                'article_not_found' => 'Article non trouvé dans cet inventaire',
            ],
        ],
        'cancel' => 'Annuler',
        'errors' => [
            'date_past' => 'La date doit être aujourd\'hui ou dans le futur',
            'zone_active' => 'Un inventaire actif existe pour cette zone',
        ],
    ],
];
