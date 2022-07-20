# Notice & Error Messages

Social sets a notice flash message on registration and login, and if there is an error, it will return an error flash message.

Copy these lines to your layout template to start showing notices and errors on the front-end:
    
```twig
<div class="notifications">
    {% for type in ['notice', 'error'] %}
        {% set message = craft.app.session.getFlash(type) %}
        {% if message %}
            <div class="notification {{ type }}">{{ message }}</div>
        {% endif %}
    {% endfor %}
</div>
```
    
See this [layout template’s source code](https://github.com/dukt/social-demo/blob/v2/craft/templates/social/_layouts/site.html#L68) in the [dukt/social-demo](https://github.com/dukt/social-demo/) repository.