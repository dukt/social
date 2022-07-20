# Logout

To log a user out, use the [logoutUrl](https://craftcms.com/docs/templating/global-variables#logoutUrl) global variable provided by Craft:
 
```twig
{% if currentUser %}
    <a href="{{ logoutUrl }}">Logout</a>
{% endif %}
```

## Custom Redirect

By default, Craft will redirect the user to the homepage after logging out. To customize this behavior, use Craft’s [postLogoutRedirect](https://docs.craftcms.com/api/v3/craft-config-generalconfig.html#property-postlogoutredirect) general config setting:

```php
'postLogoutRedirect' => 'members'
```
