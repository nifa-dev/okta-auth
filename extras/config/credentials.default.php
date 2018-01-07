<?php

use Cake\Core\Configure;

$config = [

    'Okta' => [
        'clientId' => '',
        'clientSecret' => '',
        'domain' => '',
        'tokenUrl' => '',
        'redirectUrl' => '',
        'leeway' => 0,
        'apiKey' => '',
        'authorizationServerId' => '',
        'usersModel' => 'Users'
    ]
];
return $config;