<?php

return [
    'auth' => [
        'login' => [
            'titlePage' => 'Login',
            'form' => [
                'email' => [
                    'label' => 'Email address',
                ],
                'password' => [
                    'label' => 'Password',
                    'confirm_password' => 'Confirm password',
                ],
                'rememberMe' => [
                    'label' => 'Remember me',
                ],
                'submit' => 'Sign in',
            ],
        ],
        'user' => [
            'titlePage' => 'User management',
            'email' => 'Email',
            'roles' => 'Roles',
            'status' => 'Status',
            'createdAt' => 'Created at',
            'active' => 'Active',
            'disabled' => 'Disabled',
        ],
        'reset_password' => [
            'titlePage' => 'Reset password',
            'form' => [
                'password' => [
                    'label' => 'New password',
                    'confirm_password' => 'Confirm new password',
                    'not_blank' => 'Password cannot be blank',
                    'min_length' => 'Password must be at least {{ limit }} characters long',
                    'compromised' => 'This password has been leaked in a data breach, it must not be used',
                ],
                'submit' => 'Reset password',
            ],
            'token' => [
                'invalid' => 'The password reset token is invalid or has expired.',
            ],
            'success' => 'Password reset successfully.',
            'error' => [
                'unexpected' => 'An unexpected error occurred while resetting the password.',
            ],
        ],
    ],
];
