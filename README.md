# Craft Social Login

Social Login with popular web services like Facebook, Google, Twitter, and more.

-------------------------------------------

## Table of Contents

- [Introduction](#introduction)
- [Getting Started](#getting-started)
    - [Requirements](#requirements)
    - [Installation](#installation)
    - [Configuration](#configuration)
- [Login Providers](#login-providers)
    - [Facebook](#facebook)
    - [Google](#google)
    - [Twitter](#twitter)
    - [Custom](#custom)
- [Templating](#templating)
    - [Login](#login)
    - [Logout](#logout)
    - [Managing Login Accounts](#managing-login-accounts)


## Introduction

- Visitors can social login and register with their favorite social provider
- Craft users can link to and unlink from accounts they want to be able to social login with
- When registering with a social provider, the user's profile is automatically filled with the data retrieved from his social account
- You can decide which social account fields will map with Craft user fields through config variables

### Beta Warning

This plugin is still under development, please do not use on production.

## Getting Started

### Requirements

- [Craft 2.5](https://craftcms.com/)
- [Craft OAuth 1.0](https://dukt.net/craft/oauth)

### Installation

1. Download the latest release of the plugin
2. Drop the `social` plugin folder to `craft/plugins`
3. Install Social Login plugin from the control panel in `Settings > Plugins`

### Configuration

#### allowEmailMatch

    'allowEmailMatch' => false

Will connect a social user to an existing Craft if the email provided by the social provider matches the email of a Craft user.

**Be extra carefull** with this config because it can make your site easily hackable.
If the service you use for social login doesn't check the email of their customers, anyone could easily change their address to use the same one as one of your Craft users and get in straightaway.

Double check and double test all of the possible scenarios to make sure your social login process is as secure as possible.

**If you don't know what you're doing, please keep that setting set to `false`.**


#### profileFieldsMapping

Map fields from social accounts with Craft users fields to autofill user data on registration.

    'profileFieldsMapping' => [
        'facebook' => [
            'gender' => '{{ gender }}',
        ],
    ],

## Login Providers

The following login providers are natively supported by Craft Social:

- [Facebook](#facebook)
- [Google](#google)
- [Twitter](#twitter)

The following third-party providers are provided as Craft plugins:

- [GitHub](https://github.com/dukt/craft-github) by [Dukt](https://dukt.net/)
- [LinkedIn](https://github.com/dukt/craft-linkedin) by [Dukt](https://dukt.net/)
- [Slack](https://github.com/dukt/craft-slack) by [Dukt](https://dukt.net/)

Once installed, the provider will appear in Social Login's settings.

You can add support for a custom provider by creating a Craft plugin compatible with Social Login.

If you have developed a social provider and want it to be added to this list, please contact us as: [support@dukt.net](mailto:support@dukt.net)

### Facebook

1. Go to [Facebook API Manager](https://developers.facebook.com/apps).
1. Create a new Facebook application: `My Apps > Add a new App > Website`
1. Once created, configure the Facebook App's OAuth settings: `FB App > Settings > Advanced > Client OAuth Settings`
1. Enable **Client Login** and **Web OAuth Login**
1. Fill the **Valid OAuth redirect URIs** field with the Redirect URI found in in `Craft CP > Settings > OAuth > Facebook` (Example: http://playground.dev/index.php/actions/oauth/connect)
1. Use the client ID & secret from the OAuth application that you've just created to configure Craft OAuth's Facebook provider in `Craft CP > Settings > OAuth > Facebook`
1. Social login is now setup and available for Facebook

### Google

1. Go to [Google Console](https://code.google.com/apis/console/).
1. Create a new Google application
1. Then go to `Your Application > API Manager > Credentials` and create a new **OAuth client ID** of type **Web Application**
1. Fill `Authorized JavaScript origins` and `Authorized redirect URIs` fields with the informations found in `Craft CP > Settings > OAuth > Google` and save.
1. Use the client ID & secret from the OAuth application that you've just created to configure Craft OAuth's Google provider in `Craft CP > Settings > OAuth > Google`
1. Social login is now setup and available for Google


### Twitter

1. Go to [Twitter Application Manager](https://dev.twitter.com/apps).
1. Create a new Twitter application: `Create New App`
1. Fill all required fields
1. Fill `Callback URL` field with the Redirect URI found in in `Craft CP > Settings > OAuth > Twitter` (Example: http://playground.dev/index.php/actions/oauth/connect)
1. Agree the terms and save the application
1. Use the Consumer Key & secret from the OAuth application that you've just created to configure Craft OAuth's Twitter provider in `Craft CP > Settings > OAuth > Twitter`
1. Go to [https://support.twitter.com/forms/platform](https://support.twitter.com/forms/platform)
1. Click `I need access to special permissions` and fill your application details.
1. In `Permissions Requested` ask for `email`
1. Twitter will send you an email to confirm that you have email access (usually takes ~15min)
1. Social login is now setup and available for Twitter


### Custom

Social Login is extensible and supports third-party login providers.

You can take [Craft GitHub](https://github.com/dukt/craft-github) or [Craft Slack](https://github.com/dukt/craft-slack) as a starting point for create your own login provider.


## Templating

### Login

    <a href="{{ craft.social.loginUrl('google') }}">Login with Google</a>


### Logout

    <a href="{{ craft.social.logoutUrl() }}">Logout</a></li>

### Managing Login Accounts

    <h2>Login Accounts</h2>

    {% for provider in craft.social.getLoginProviders() %}

        {% set account = craft.social.getLoginAccountByLoginProvider(provider.handle) %}

        <h4>{{ provider.name }}</h4>

        <p>
            {% if account %}
                You can login using {{ provider.name }}.
            {% else %}
                {{ provider.name }} login is disabled for your account.
            {% endif %}
        </p>

        <p>
            {% if account %}
                <a href="{{craft.social.getLoginAccountDisconnectUrl(loginProvider.handle)}}">Disable Social Login with {{ loginProvider.name }}</a>
            {% else %}
                <a href="{{ craft.social.getLoginAccountConnectUrl(loginProvider.handle) }}">Enable Social Login with {{ loginProvider.name }}</a>
            {% endif %}
        </p>

    {% endfor %}