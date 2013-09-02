# Social <small>_for Craft CMS_</small>

Social plugin let's you login to Craft CMS with popular service providers.

- [Installation](#install)
- [Supported providers](#providers)
- [Templating](#templating)
    - [Login with a single OAuth provider](#template-login)
    - [Login with multiple OAuth providers](#template-login-multiple)
    - [Provider account profile](#template-account)
    - [Manage Apps](#template-apps)

- [API Reference](#api)

<a id="install"></a>
## Installation

1. Unzip and drop the Social plugin in your `craft/plugin` directory.

2. Go to **Admin / Social** and follow the installation instructions.

<a id="providers"></a>
## Supported providers

- Facebook
- GitHub
- Google
- Twitter
- Flickr

<a id="templating"></a>
## Templating

<a id="template-login"></a>
### Login with an OAuth provider

    {% set provider = 'Facebook' %}

    <p><a href="{{ craft.social.login(provider) }}">Login with {{provider}}</a></p>


<a id="template-login-multiple"></a>
### Login with multiple OAuth providers

    <table border="1">
        {% for provider in craft.social.getProviders() %}

            {% if provider.account %}
                <tr>
                    <td>{{provider.name}}</td>
                    <td>{{provider.account.email}}</td>
                    <td><a href="{{craft.social.logout(provider.classHandle)}}">Disconnect</a></td>
                </tr>
            {% else %}
                <tr>
                    <td>{{provider.name}}</td>
                    <td><em>Not authenticated</em></td>
                    <td>
                        {% if provider.isConfigured() %}
                            <a href="{{ craft.social.login(provider.classHandle) }}">Authenticate</a>
                        {% else %}
                            <em>Authentication disabled</em>
                        {% endif %}
                    </td>
                </tr>
            {% endif %}

        {% endfor %}

        {% for provider in craft.oauth.getProviders() %}

            {% set account = craft.social.getAccount(provider) %}

            {% if account %}
                <tr>
                    <td>{{provider}}</td>
                    <td>{{account.email}}</td>
                    <td><a href="{{craft.social.logout(provider)}}">Disconnect</a></td>
                </tr>
            {% else %}
                <tr>
                    <td>{{provider}}</td>
                    <td><em>Not authenticated</em></td>
                    <td>
                        {% if craft.oauth.providerIsConfigured(provider) %}
                            <a href="{{ craft.social.login(provider) }}">Authenticate</a>
                        {% else %}
                            <em>Authentication disabled</em>
                        {% endif %}
                    </td>
                </tr>
            {% endif %}
        {% endfor %}
    </table>


<a id="template-account"></a>
### Provider account profile


<a id="template-apps"></a>
### Manage Apps



<a id="api"></a>
## API Reference

### craft.social.login(providerClass)

Return a link for logging in with given provider.

### craft.social.logout(providerClass)

Returns a link for logging out.

### craft.social.getProviders()

Returns all providers as an array.

### craft.social.getAccount(providerClass)

Get account data from a provider.