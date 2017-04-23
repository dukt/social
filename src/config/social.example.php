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
     * User mapping
     */
    'userMapping' => [
        'twitter' => [
            'location' => '{{ location }}',
            'profileUrl' => '{{ nickname }}',
        ],

        'facebook' => [
            'firstName' => '{{ firstName }}',
            'lastName' => '{{ lastName }}',
            'location' => '{{ locationName }}',
            'gender' => '{{ gender }}',
            'profileUrl' => '{{ link }}',
        ]
    ],

    /**
     * Login providers
     */
    'loginProviders' => [
        'google' => [
            'clientId' => 'OAUTH_CLIENT_ID',
            'clientSecret' => 'OAUTH_CLIENT_SECRET'
        ],
        'facebook' => [
            'clientId' => 'OAUTH_CLIENT_ID',
            'clientSecret' => 'OAUTH_CLIENT_SECRET'
        ],
        'twitter' => [
            'clientId' => 'OAUTH_CLIENT_ID',
            'clientSecret' => 'OAUTH_CLIENT_SECRET'
        ]
    ]
];
