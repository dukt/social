# Social Login for Craft CMS

A simple plugin to connect to YouTube's API.

-------------------------------------------

## Beta Warning

This plugin is still under development, please do not use on production.


## Installation

1. Download the latest release of the plugin
2. Drop the `social` plugin folder to `craft/plugins`
3. Install Social Login plugin from the control panel in `Settings > Plugins`


## Templating


### Login

    <a href="{{ craft.social.loginUrl('google') }}">Login with Google</a>


### Logout

    <a href="{{ craft.social.logoutUrl() }}">Logout</a></li>