# Configuration

In addition to the settings available in **CP → Settings → Social**, the config items below can be placed into a social.php file in your craft/config directory:

## allowEmailMatch

Allow email matching.

```php
'allowEmailMatch' => false,
```

## autoFillProfile

Automatically fills user fields when a user registers, based on userFieldMapping config variables.

```php
'autoFillProfile' => true,
```

## defaultGroup

User group users will be added to when registering with a social service.

```php
'defaultGroup' => 1,
```

## enableCpLogin

Enable Social Login buttons on the Control Panel's login screen.

```php
'enableCpLogin' => false,
```

## enabledLoginProviders

Enable Social Login buttons on the Control Panel's login screen.

```php
'enabledLoginProviders' => [],
```

## enableSocialLogin

When disabled, users are not be able to social login or register.

```php
'enableSocialLogin' => true,
```

## enableSocialRegistration

When disabled, users will not able to register with a social service, but will still be able to social login to an existing account.

```php
'enableSocialRegistration' => true,
```

## lockDomains
Locks social registration to specific domains. The list of locked domains must be provided as as array.

```php
'lockDomains' => [],
```

## loginProviders

Defines the [user field mapping](registration.md#user-field-mapping), the profile fields and the OAuth configuration for login providers.

```php
<?php

return array(
    'loginProviders' => [
        'facebook' => [
            'userFieldMapping' => [
                'gender' => '{{ profile.gender }}',
                'birthday' => '{{ profile.toArray().birthday }}',
            ],
            'profileFields' => [
                'birthday',
            ],
            'oauth' => [
                'options' => [
                    'clientId' => 'CLIENT_ID',
                    'clientSecret' => 'CLIENT_SECRET',    
                ],
                'scope' => [
                    'SCOPE_1', 
                    'SCOPE_2'
                ],       
                'authorizationOptions' => [
                    'OPTION_1' => 'VALUE_1', 
                    'OPTION_2' => 'VALUE_2',
                ]
            ],
        ]
    ]
);
```
