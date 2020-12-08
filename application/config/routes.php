<?php

return [
    '' => [
        'controller' => 'main',
        'action' => 'index'
    ],
    'api/create_user' => [
        'controller' => 'User',
        'action' => 'create'
    ],

    'api/login' => [
        'controller' => 'User',
        'action' => 'login'
    ],
    'api/validate_token' => [
        'controller' => 'User',
        'action' => 'validate'
    ],
];


