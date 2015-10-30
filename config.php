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
	 * Require Email Address
	 */
    'requireEmail' => true,

	/**
	 * Complete Registration Template
	 */
    'completeRegistrationTemplate' => null,

    /**
     * Auto-fill rules / Profile Fields Mapping
     */
    'profileFieldsMapping' => [
        'facebook' => [
            'gender' => '{{ gender }}',
        ],
    ],

	/**
	 * OAuth Configuration
	 */
     'oauthConfig' => [
        'google' => [
            'options' => [
                'access_type' => 'offline',
                'approval_prompt' => 'force'
            ]
        ]
     ]
];
