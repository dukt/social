# Social <small>_for Craft CMS_</small>

Social plugin let's you connect to Craft CMS with the most popular service providers : Google, Facebook, Twitter, Flickr, ...

- [Installation](#install)
- [Supported providers](#providers)
- [Login with OAuth providers](#login)
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

<a id="login"></a>
## Login with OAuth providers

    <table border="1">
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