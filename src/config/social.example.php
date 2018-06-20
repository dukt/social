<?php

return [
    /**
     * Allow Email Match
     */
    'allowEmailMatch' => false,


    /**
     * Lock social registration to specific domains
     */
    'lockDomains' => [],

    /**
     * Login providers
     */
    'loginProviders' => [
        'google' => [
            'clientId' => 'OAUTH_CLIENT_ID',
            'clientSecret' => 'OAUTH_CLIENT_SECRET',
            'userFieldMapping' => [],
        ],
        'facebook' => [
            'clientId' => 'OAUTH_CLIENT_ID',
            'clientSecret' => 'OAUTH_CLIENT_SECRET',
            // 'graphApiVersion' => 'v2.12',
            'userFieldMapping' => [
                'firstName' => '{{ firstName }}',
                'lastName' => '{{ lastName }}',
                'location' => '{{ locationName }}',
                'gender' => '{{ gender }}',
                'profileUrl' => '{{ link }}',
            ],
        ],
        'twitter' => [
            'clientId' => 'OAUTH_CLIENT_ID',
            'clientSecret' => 'OAUTH_CLIENT_SECRET',
            'userFieldMapping' => [
                'location' => '{{ location }}',
                'profileUrl' => '{{ nickname }}',
            ],
        ]
    ]
];
