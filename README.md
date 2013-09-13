# Social <small>_for Craft CMS_</small>

Let people login and register to your Craft website from their Facebook, GitHub, Google, Twitter or Flickr account.

- [Installation](#install)
- [Supported providers](#providers)
- [Login](#login)
    - [Login with Facebook](#login-facebook)
    - [Login with GitHub](#login-github)
    - [Login with Google](#login-google)
    - [Login with Twitter](#login-twitter)
    - [Login with Flickr](#login-flickr)
    - [Login with multiple providers](#login-multiple)
- [Logout](#logout)
- [Templating Reference](#templating)
- [SocialService API](#api)
- [Licensing](#license)
- [Feedback](#feedback)

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
### Login

<a id="login-facebook"></a>
#### Login with Facebook

    {% set provider = 'Facebook' %}
    {% set redirect = 'account' %}

    <p><a href="{{ craft.social.login(provider, redirect) }}">Login with {{provider}}</a></p>


<a id="login-github"></a>
#### Login with GitHub

    {% set provider = 'Github' %}
    {% set redirect = 'account' %}

    <p><a href="{{ craft.social.login(provider, redirect) }}">Login with {{provider}}</a></p>


<a id="login-google"></a>
#### Login with Google

    {% set provider = 'Google' %}
    {% set redirect = 'account' %}

    <p><a href="{{ craft.social.login(provider, redirect) }}">Login with {{provider}}</a></p>


<a id="login-twitter"></a>
#### Login with Twitter

    {% set provider = 'Twitter' %}
    {% set redirect = 'account' %}

    <p><a href="{{ craft.social.login(provider, redirect) }}">Login with {{provider}}</a></p>


<a id="login-flickr"></a>
#### Login with Flickr

    {% set provider = 'Flickr' %}
    {% set redirect = 'account' %}

    <p><a href="{{ craft.social.login(provider, redirect) }}">Login with {{provider}}</a></p>

<a id="login-multiple"></a>
#### Login with multiple providers

    {% for provider in craft.oauth.getProviders() %}
        <p>
            <a href="{{ craft.social.login(provider.classHandle, 'account') }}">
                Login with {{provider.classHandle}}
            </a>
        </p>
    {% endfor %}


<a id="logout"></a>
### Logout

    {% set redirect = '' %}

    <p><a href="{{ craft.social.logout(redirect) }}">Logout</a></p>



<a id="templating"></a>
## Templating Reference

<dl>
    <dt><tt>login(providerClass, redirect = null, scope = null)</tt></dt>
    <dd>
        <pre><code>{{craft.social.login('Facebook', 'account')}}</code></pre>

        <p>Return a link for logging in with given provider.</p>
    </dd>
</dl>

<dl>
    <dt><tt>logout(redirect = null)</tt></dt>
    <dd>
        <pre><code>{{craft.social.logout()}}</code></pre>

        <p>Returns a link for logging out.</p>
    </dd>
</dl>

<a id="api"></a>
## SocialService API

<dl>
    <dt><tt>login($providerClass, $redirect = null, $scope = null)</tt></dt>
</dl>

<dl>
    <dt><tt>logout($redirect = null)</tt></dt>
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
