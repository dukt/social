# Login

## Usage
Use the `craft.social.getLoginUrl()` method to show a link that will register new users and authenticate existing ones.

```twig
<a href="{{ craft.social.getLoginUrl('google') }}">Login with Google</a>
```

## Custom Redirect
You can customize the URL the user will be redirected to after login.   

```twig
{% set redirect = 'account' %}

<a href="{{ craft.social.getLoginUrl('google', { redirect: redirect }) }}">Login with Google</a>
```

## Multiple Providers
Sometimes you will want to show all of the enabled login providers users can login with:

```twig
{% for provider in craft.social.getLoginProviders() %}
    <p>
        <a href="{{ craft.social.getLoginUrl(provider.handle) }}">
            Login with {{ provider.name }}
        </a>
    </p>
{% endfor %}
```
