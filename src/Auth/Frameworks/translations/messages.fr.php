<?php

return [
    'auth' => [
        'login' => [
            'titlePage' => 'Connexion',
            'form' => [
                'email' => [
                    'label' => 'Adresse email',
                ],
                'password' => [
                    'label' => 'Mot de passe',
                    'confirm_password' => 'Confirmer le mot de passe',
                ],
                'rememberMe' => [
                    'label' => 'Se souvenir de moi',
                ],
                'submit' => 'Se connecter',
            ],
        ],
        'user' => [
            'titlePage' => 'Gestion des utilisateurs',
            'email' => 'Email',
            'roles' => 'Rôles',
            'status' => 'Statut',
            'createdAt' => 'Date de création',
            'active' => 'Actif',
            'disabled' => 'Désactivé',
        ],
        'reset_password' => [
            'titlePage' => 'Réinitialisation du mot de passe',
            'form' => [
                'password' => [
                    'label' => 'Nouveau mot de passe',
                    'confirm_password' => 'Confirmer le nouveau mot de passe',
                    'not_blank' => 'Le mot de passe ne peut pas être vide',
                    'min_length' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                    'compromised' => 'Ce mot de passe a été divulgué lors d\'une fuite de données, il ne doit plus être utilisé',
                ],
                'submit' => 'Réinitialiser le mot de passe',
            ],
            'token' => [
                'invalid' => 'Le token de réinitialisation de mot de passe est invalide ou a expiré.',
            ],
            'success' => 'Mot de passe réinitialisé avec succès.',
            'error' => [
                'unexpected' => 'Une erreur inattendue est survenue lors de la réinitialisation du mot de passe.',
            ],
        ],
    ],
];
