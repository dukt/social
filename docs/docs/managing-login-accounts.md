# Managing Login Accounts

Let users manage their login accounts, list all login providers and let them connect or disconnect their account from the front-end.

## Retrieve login providers

Get list of enabled login providers.

```twig
{% set loginProviders = craft.social.getLoginProviders() %}
```

To retrieve disabled login providers as well, you will need to pass `false` as a parameter.

```twig
{% set loginProviders = craft.social.getLoginProviders(false) %}
```

## Retrieve login accounts

Retrieve login account elements based on criterias.

```twig
{% set loginAccounts = craft.social.loginAccounts({
    providerHandle: 'google',
}).all() %}
```

### Query Parameters

Param              | Accepts                              | Description
------------------ | ------------------------------------ | ---------------------------------------------------------------------------------
`userId`		   | `int\|int[]\|null`					  | The user ID(s) that the resulting login accounts must belong to
`providerHandle`   | `string\|null`						  | The handle of the provider that the resulting login accounts must belong to
`socialUid`        | `string\|null`						  | The socialUid of the login account
`username`		   | `string\|null`						  | The username of the user that the resulting login accounts must belong to
`email`			   | `string\|null`						  | The email of the user that the resulting login accounts must belong to
`firstName`		   | `string\|null`						  | The firstName of the user that the resulting login accounts must belong to
`lastName`		   | `string\|null`						  | The handle of the provider that the resulting login accounts must belong to
`lastLoginDate`	   | `mixed`                              | The last time the user has logged in


## Retrieve a login account

Get a login account from its login provider’s handle.

```twig
{% set loginAccount = craft.social.getLoginAccountByLoginProvider('google') %}
```

## Connect to a login provider

Get login account connect URL by login provider handle.

```twig
<a href="{{ craft.social.getLoginAccountConnectUrl('google') }}>Enable social login with Google</a>
```

## Disconnect from a login provider

Get login account disconnect URL by login provider handle.

```twig
<a href="{{ craft.social.getLoginAccountDisconnectUrl('google') }}">Disable social login with Google</a>
```

## Example

This example lists enabled login providers and shows a “Enable/Disable Social Login” button for each one of them. 

```twig
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
            <a href="{{ craft.social.getLoginAccountDisconnectUrl(provider.handle) }}">Disable Social Login with {{ provider.name }}</a>
        {% else %}
            <a href="{{ craft.social.getLoginAccountConnectUrl(provider.handle) }}">Enable Social Login with {{ provider.name }}</a>
        {% endif %}
    </p>
{% endfor %}
```
