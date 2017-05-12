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
            'userMapping' => [],
        ],
        'facebook' => [
            'clientId' => 'OAUTH_CLIENT_ID',
            'clientSecret' => 'OAUTH_CLIENT_SECRET',
            'userMapping' => [
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
            'userMapping' => [
                'location' => '{{ location }}',
                'profileUrl' => '{{ nickname }}',
            ],
        ]
    ]
];
