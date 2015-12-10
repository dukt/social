# Craft Social Login

Social Login with popular web services like Facebook, Google, Twitter, and more.

-------------------------------------------

## Table of Contents

- [Beta Warning](#beta-warning)
- [Getting Started](#getting-started)
    - [Introduction](#introduction)
    - [Requirements](#requirements)
    - [Installation](#installation)
    - [Configuration](#configuration)
- [Login Providers](#login-providers)
- [Templating](#templating)


## Beta Warning

This plugin is still under development, please do not use on production.

## Getting Started

### Introduction

- Visitors can social login and register with their favorite social provider
- Craft users can link to and unlink from accounts they want to be able to social login with
- When registering with a social provider, the user's profile is automatically filled with the data retrieved from his social account
- You can decide which social account fields will map with Craft user fields through config variables

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

### Login Providers

#### Native

The following login providers are natively supported by Craft Social:

- Facebook
- Google
- Twitter

#### Third-party

The following third-party providers are provided as Craft plugins:

- [GitHub](https://dukt.net/craft/github) by [Dukt](https://dukt.net/)
- [LinkedIn](https://dukt.net/craft/linkedin) by [Dukt](https://dukt.net/)
- [Slack](https://dukt.net/craft/slack) by [Dukt](https://dukt.net/)

Once installed, the provider will appear in Social Login's settings.

You can add support for a custom provider by creating a Craft plugin compatible with Social Login.

If you have developed a social provider and want it to be added to this list, please contact us as: [support@dukt.net](mailto:support@dukt.net)

#### Creating a provider

More detailed instructions are upcoming for helping you creating your own custom social providers.

For now, you can still take a look at GitHub's integration to see how a Craft plugin can add a social provider.

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