<?php

return [

    /**
     * Advanced Mode
     */
    'advancedMode' => true,

    /**
     * Show CP Section
     */
    'showCpSection' => true,

    /**
     * Allow Email Match
     */
    'allowEmailMatch' => false,

    /**
     * User Mapping
     */
    'userMapping' => [
        'username' => '{{ email }}',
        'email' => '{{ email }}',
        'firstName' => '{{ firstName }}',
        'lastName' => '{{ lastName }}',
    ],

    /**
     * Twitter User Mapping
     */
    // 'twitterUserMapping' => [
    //     'username' => '{{ nickname }}',
    // ],

    /**
     * Facebook User Content Mapping
     */
    // 'facebookUserContentMapping' => [
    //     'gender' => '{{ gender }}',
    // ],

    /**
     * Google Login Provider
     */
    // 'googleLoginProvider' => [
    //     'authorizationOptions' => [
    //         'hd' => 'mycompany.com'
    //     ],
    // ],
];
