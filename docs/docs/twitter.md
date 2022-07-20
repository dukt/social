# Twitter

Follow these steps to configure Twitter for social login:

## OAuth Configuration

### Step 1: Create a new app
1. Go to the [Twitter Application Manager](https://dev.twitter.com/apps).
1. Click “Create New App” to create a new Twitter application.
1. Fill all required fields.
1. Go to **Craft Control Panel → Settings → Social → Login Providers → Twitter** and locate the Redirect URI.
1. If the Redirect URI contains query parameters, change Craft’s [usePathInfo](https://docs.craftcms.com/v3/config/config-settings.html#usepathinfo) config to `true` to use `PATH_INFO` to specify the path as Twitter [doesn’t allow query parameters in callback URLs](https://developer.twitter.com/en/docs/basics/apps/guides/callback-urls) anymore.
1. Copy the Redirect URI to the Twitter application’s “Callback URL” field.
1. Agree to the terms and save the application.

### Step 2: Setup app permissions
1. First, you need to [contact Twitter to whitelist your app](https://support.twitter.com/forms/platform) to be able to request a user’s email.
1. Click “I need access to special permissions” and fill your application details.
1. In **Permissions Requested** ask for the “email” special permission.
1. Twitter will send you an email to confirm that you have email access (it usually takes less than 24 hours).
1. Now go back to the Twitter Application manager and click on the app that you've just created to edit it.
1. Under **Permissions → Access**, select “Read and write” (don’t choose the one that gives access to Direct Messages otherwise social login will fail).
1. Under **Permissions → Additional Permissions**, check the **Request email addresses from users** box (this will only be visible once Twitter has whitelisted your app).

### Step 3: OAuth settings in Craft
1. Twitter will provide you a consumer key and a consumer secret for your application, copy them to **Craft Control Panel → Settings → Social → Login Providers → Twitter → OAuth**, and use them as client ID and client secret values.
1. Go to **Craft Control Panel → Settings → Social → Login Providers** and enable Twitter.

🎉


## Default User Field Mapping

The Twitter login provider defines the following user field mapping by default.

```php
[
    'id' => '{{ profile.uid }}',
    'email' => '{{ profile.email }}',
    'username' => '{{ profile.email }}',
    'photo' => '{{ profile.imageUrl|replace("_normal.", ".") }}',
]
```

You can override and extend the default mapping using the [loginProviders](configuration.md#loginproviders) config.

## Profile Object
The profile response for the Twitter login provider is an OAuth 1 [User](https://github.com/thephpleague/oauth1-client/blob/master/src/Client/Server/User.php) object.

### Properties
- `uid`
- `nickname`
- `name`
- `firstName`
- `lastName`
- `email`
- `location`
- `description`
- `imageUrl`
- `urls`
- `extra`

### Methods
- `getIterator()`
