# Facebook

Follow these steps to configure Facebook for social login:

## OAuth Configuration

1. Go to the [Facebook API Manager](https://developers.facebook.com/apps).
1. Click the “Add a New App” button to create a new Facebook application.
1. Once created, go to your application and set up the “Facebook Login” product.
1. Go to **Facebook API Manager → Your App → Facebook Login → Settings**, fill the “Valid OAuth redirect URIs” field with the Redirect URI found in **Craft Control Panel → Settings → Social → Login Providers → Facebook**, and save.
1. Go to **Facebook API Manager → Your App → Settings → Basic** and copy the App ID and App Secret to **Craft Control Panel → Settings → Social → Login Providers → Facebook → OAuth**, and use them as client ID and client secret values.
1. Go to **Craft Control Panel → Settings → Social → Login Providers** and enable Facebook.

🎉

⚠️ **Warning:** Facebook user IDs are only unique per Facebook API app. 
Using a different Facebook API app app (changing the client ID), will most of the time break the relation between a Craft user and a Facebook user. 
It will also prevent people which have already setup their accounts to login using Facebook for logging in, and the plugin will think that the email is already registered for a different Facebook user ID.

## Default User Field Mapping

The Facebook login provider defines the following user field mapping by default.

```php
[
    'id' => '{{ profile.getId() }}',
    'email' => '{{ profile.getEmail() }}',
    'username' => '{{ profile.getEmail() }}',
    'photo' => '{{ profile.getPictureUrl() }}',
]
```

You can override and extend the default mapping using the [loginProviders](configuration.md#loginproviders) config.

## Profile Object
The profile response for the Facebook login provider is a [FacebookUser](https://github.com/thephpleague/oauth2-facebook/blob/master/src/Provider/FacebookUser.php) object.

### Methods
- `getId()`
- `getName()`
- `getFirstName()`
- `getLastName()`
- `getEmail()`
- `getHometown()`
- `getBio()`
- `isDefaultPicture()`
- `getPictureUrl()`
- `getCoverPhotoUrl()`
- `getGender()`
- `getLocale()`
- `getLink()`
- `getTimezone()`
- `getMinAge()`
- `getMaxAge()`
- `toArray()`

### Additional Profile Fields

Sometimes, you might need more than just the default profile fields. You can adjust your configuration to request additional fields, like the user’s birthday, location and more.

#### OAuth Scope
The first to do is to extend the permissions requested by the plugin.
For example, to get the permission to request the user’s birthday, we would need the `user_birthday` permission.

By default, the following permissions are being requested:

```php
[
    'email',
]
```
[See Facebook permissions](https://developers.facebook.com/docs/facebook-login/permissions)

⚠️ **Warning:** If your app asks for more than `public_profile` and `email`, it will require [review](https://developers.facebook.com/docs/facebook-login/permissions/review) by Facebook before your app can be used by people other than the app's developers. 


#### Profile Fields
In addition to defining an additional OAuth scope, we need the plugin to ask for extra fields when requesting the profile’s data.
For example, to request the user’s birthday field, we would need to add the `birthday` field to the list of requested profile fields.

By default, the following fields are being requested:

```php
[
    'id',
    'name',
    'first_name',
    'last_name',
    'email',
    'picture.type(large){url,is_silhouette}',
    'cover{source}',
    'gender',
    'locale',
    'link',
]
```

[See Facebook User](https://developers.facebook.com/docs/graph-api/reference/user)

#### User Field Mapping
If you request additional fields, they probably won't be supported by the [FacebookUser](https://github.com/thephpleague/oauth2-facebook/blob/master/src/Provider/FacebookUser.php) object which only supports the default fields.
To access the additional fields, you need to use the `toArray()` method to access `FacebookUser`’s raw response data.

```twig
{{ profile.toArray().birthday }}
```

#### Configuration Example

```php
<?php

return [
    'loginProviders' => [
        'facebook' => [
            'oauth' => [
                'scope' => [
                    'user_birthday'
                ]
            ],
            'profileFields' => [
                'birthday',
            ],
            'userFieldMapping' => [
                'birthday' => '{{ profile.toArray().birthday }}',
            ],
        ]
    ]
];
```