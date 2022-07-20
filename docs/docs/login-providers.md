# About Login Providers

## OAuth Configuration

You need to setup OAuth before using a login provider. The configuration steps are different from one provider to another, so make sure to check the [login provider’s page](#supported-providers) to see how to setup OAuth.

## User Field Mapping

When a user registers on your website with Social login, the data from the user’s social profile can be used to fill the Craft user’s fields.  

By default, login providers automatically map some fields like the email or the username. 

To customize the default user field mapping, create a `craft/config/social.php` config file, and define a `loginProviders.{providerHandle}.userFieldMapping` array for your provider.

The `userFieldMapping` array should have Craft user field handles as keys, and a template string as value where the `profile` object can be used.

_Each login provider returns a different profile object, so check the [login provider’s page](#supported-providers) page to see which profile object it returns._

```php
<?php


return [
    'loginProviders' => [
        'facebook' => [            
            'userFieldMapping' => [
                'gender' => '{{ profile.getGender() }}',
            ],
        ],
    ]
];
```

## Supported Providers

### Core
- [Facebook](facebook.md)
- [Google](google.md)
- [Twitter](twitter.md)

### First-Party
- [GitHub](https://github.com/dukt/social-github)
- [LinkedIn](https://github.com/dukt/social-linkedin)

### Third-Party
Email us at [support@dukt.net](mailto:support@dukt.net) to add your login provider to the list.
