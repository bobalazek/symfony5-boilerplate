<?php

return [
    [
        'name' => 'Corco',
        'username' => 'corco',
        'email' => 'corco@corcosoft.com',
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
        'email' => 'admin@corcosoft.com',
        'password' => 'password',
        'roles' => ['ROLE_ADMIN'],
    ],
    [
        'name' => 'User moderator',
        'username' => 'usermoderator',
        'email' => 'usermoderator@corcosoft.com',
        'password' => 'password',
        'roles' => ['ROLE_USER_MODERATOR'],
    ],
    [
        'name' => 'User',
        'username' => 'user',
        'email' => 'user@corcosoft.com',
        'password' => 'password',
        'roles' => ['ROLE_USER'],
    ],
    [
        'name' => 'User2',
        'username' => 'user2',
        'email' => 'user2@corcosoft.com',
        'password' => 'password',
        'roles' => ['ROLE_USER'],
    ],
];
