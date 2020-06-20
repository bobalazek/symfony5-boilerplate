<?php

use App\Entity\UserTfaMethod;

return [
    [
        'name' => 'Corco',
        'username' => 'corco',
        'email' => 'corco@corcoviewer.com',
        'password' => substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, 16),
        'roles' => ['ROLE_USER'],
    ],
    [
        'name' => 'Borut',
        'username' => 'bobalazek',
        'email' => 'bobalazek124@gmail.com',
        'password' => 'password',
        'roles' => ['ROLE_SUPER_ADMIN'],
    ],
    [
        'name' => 'Ana',
        'username' => 'ana',
        'email' => 'anakociper124@gmail.com',
        'password' => 'password',
        'roles' => ['ROLE_SUPER_ADMIN'],
    ],
    [
        'name' => 'Admin',
        'username' => 'admin',
        'email' => 'admin@corcoviewer.com',
        'password' => 'password',
        'roles' => ['ROLE_ADMIN'],
    ],
    [
        'name' => 'User moderator',
        'username' => 'usermoderator',
        'email' => 'usermoderator@corcoviewer.com',
        'password' => 'password',
        'roles' => ['ROLE_USER_MODERATOR'],
    ],
    [
        'name' => 'User',
        'username' => 'user',
        'email' => 'user@corcoviewer.com',
        'password' => 'password',
        'roles' => ['ROLE_USER'],
    ],
    [
        'name' => 'User2',
        'username' => 'user2',
        'email' => 'user2@corcoviewer.com',
        'password' => 'password',
        'roles' => ['ROLE_USER'],
    ],
    [
        'name' => 'User with TFA',
        'username' => 'userwithtfaemail',
        'email' => 'userwithtfa@corcoviewer.com',
        'password' => 'password',
        'roles' => ['ROLE_USER'],
        'tfa_enabled' => true,
        'tfa_methods' => [
            UserTfaMethod::METHOD_EMAIL,
        ],
    ],
];
