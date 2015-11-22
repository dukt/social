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
        'email' => '{{ email }}',
        'username' => '{{ email }}',
        'firstName' => '{{ firstName }}',
        'lastName' => '{{ lastName }}',
    ],

    /**
     * User Fields Mapping
     */
    'userFieldsMapping' => [
        // 'facebook' => [
        //     'fields' => [
        //         'gender' => '{{ gender }}',
        //     ],
        // ],
    ],

	/**
	 * OAuth Configuration
	 */
     'authorizationOptions' => [
        'google' => [
            'access_type' => 'offline',
            'approval_prompt' => 'force'
        ],
     ],
];
