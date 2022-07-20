# Registration

## Usage

Use the `craft.social.getLoginUrl()` method to show a link that will register new users and authenticate existing ones.

```twig
<a href="{{ craft.social.getLoginUrl('google') }}">Login with Google</a>
```

## User Field Mapping

When a user registers on your website with Social login, the data from the user’s social profile can be used to fill the Craft user’s fields.  

[See User Field Mapping for login providers](login-providers.md#user-field-mapping)
