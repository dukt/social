<?php

/**
 * General Configuration
 *
 * All of your system's general configuration settings go in here.
 * You can see a list of the default settings in craft/app/etc/config/defaults/general.php
 */

return [

	/**
	 * Allow Email Match
	 */
    'allowEmailMatch' => false,

    /**
     * User Mapping
     */
    'userMapping' => [
        'firstName' => '{{ firstName }}',
        'lastName' => '{{ lastName }}',
    ],

    /**
     * User Fields Mapping
     */
    'userFieldsMapping' => [
        // 'facebook' => [
        //     'gender' => '{{ gender }}',
        // ],
    ],

    /**
     * Login Providers
     */

     // 'loginProviders' => [

     //    'google' => [
     //        'scope' => [
     //            'https://www.googleapis.com/auth/userinfo.profile',
     //            'https://www.googleapis.com/auth/userinfo.email',
     //        ],
     //        'authorizationOptions' => [
     //            'access_type' => 'offline',
     //            'approval_prompt' => 'force'
     //        ]
     //    ]

     // ]
];
