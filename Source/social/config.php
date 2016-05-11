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
    'advancedMode' => false,

    /**
     * Show CP section
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
    //    'twitterUserMapping' => [
    //        'username' => '{{ nickname }}',
    //    ],

    /**
     * Facebook User Content Mapping
     */
    // 'facebookUserContentMapping' => [
    //      'gender' => '{{ gender }}',
    // ],
];
