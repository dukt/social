# Social <small>_for Craft CMS_</small>

Social plugin let's you login to Craft CMS with popular service providers.

- [Installation](#install)
- [Supported providers](#providers)
- [Login](#login)
- [Logout](#logout)
- [Advanced Usage](#advanced)
    - [Login with multiple OAuth providers](#multiple-login)
    - [Displaying account data](#account)
- [Templating Reference](#templating)
- [SocialService API](#api)
- [Licensing](#license)
- [Feedback](#feedback)

<a id="install"></a>
## Installation

1. Unzip and drop the Social plugin in your `craft/plugin` directory.

2. Go to **Admin / Social** and follow the installation instructions.

<a id="providers"></a>
## Providers

- Facebook
- GitHub

- Google
- Twitter
- Flickr

The following providers are **not supported** but will be added soon :

- Appnet
- Dropbox
- Foursquare
- Instagram
- LinkedIn
- Mailchimp
- PayPal
- Tumblr
- Vimeo


<a id="login"></a>
### Login

    {% set provider = 'Facebook' %}

    <p><a href="{{ craft.social.login(provider) }}">Login with {{provider}}</a></p>

<a id="logout"></a>
### Logout

    <p><a href="{{ craft.social.logout() }}">Logout</a></p>

<a id="advanced"></a>
### Advanced Usage

<a id="multiple-login"></a>
#### Login with multiple OAuth providers


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

<a id="account"></a>
#### Displaying account data


    {% set account = craft.social.getAccount('Facebook') %}

    <p>The email is : {{account.email}}</p>


<a id="templating"></a>
## Templating Reference

<dl>
    <dt><tt>login(providerClass, redirect = null)</tt></dt>
    <dd>
        <pre><code>{{craft.social.login(providerClass, redirect = null)}}</code></pre>

        <p>Return a link for logging in with given provider.</p>
    </dd>
</dl>

<dl>
    <dt><tt>logout(redirect = null)</tt></dt>
    <dd>
        <pre><code>{{craft.social.logout(redirect = null)}}</code></pre>

        <p>Returns a link for logging out.</p>
    </dd>
</dl>


<dl>
    <dt><tt>getProviders()</tt></dt>
    <dd>
        <pre><code>{{craft.social.getProviders()}}</code></pre>

        <p>Returns all providers as an array.</p>
    </dd>
</dl>


<dl>
    <dt><tt>getAccount(providerClass)</tt></dt>
    <dd>
        <pre><code>{{craft.social.getAccount(providerClass)}}</code></pre>

        <p>Get account data from a provider.</p>
    </dd>
</dl>


<a id="templating"></a>
## SocialService API

<dl>
    <dt><tt>login($providerClass, $redirect = null, $scope = null)</tt></dt>    
</dl>

<dl>
    <dt><tt>logout($redirect = null)</tt></dt>    
</dl>

<dl>
    <dt><tt>connect($providerClass)</tt></dt>    
</dl>

<dl>
    <dt><tt>disconnect($providerClass)</tt></dt>    
</dl>

<dl>
    <dt><tt>getAccount($providerClass)</tt></dt>    
</dl>

<dl>
    <dt><tt>getToken($providerClass)</tt></dt>    
</dl>


<a id="license"></a>
## Licensing

OAuth plugin for Craft CMS is free to use for end users.

If you are a developer and want to make use of the OAuth plugin in your plugins, please contact us at hello@dukt.net.

<a id="feedback"></a>
## Feedback

**Please provide feedback!** We want this plugin to make fit your needs as much as possible.
Please [get in touch](mailto:hello@dukt.net), and point out what you do and don't like. **No issue is too small.**

This plugin is actively maintained by [Benjamin David](https://github.com/benjamindavid), from [Dukt](http://dukt.net/).
