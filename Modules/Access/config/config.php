<?php

return [
    'name' => 'Access',

    'actions' => ['create', 'read', 'update', 'delete', 'view'],

    'resources' => [
        'users',
        'roles',
        'permissions',
        'hotels',
        'bookings',
    ],
];