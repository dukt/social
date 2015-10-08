# Social Login for Craft CMS

A simple plugin to connect to YouTube's API.

-------------------------------------------

## Beta Warning

This plugin is still under development, please do not use on production.


## Installation

1. Download the latest release of the plugin
2. Drop the `social` plugin folder to `craft/plugins`
3. Install Social Login plugin from the control panel in `Settings > Plugins`


## Config

Please keep in mind that changing the default values can expose your website to security risks.

Read the notices carefully and you will be safe and good to go !

### allowEmailMatch

    'allowEmailMatch' => false

Will connect a social user to an existing Craft if the email provided by the social gateway matches the email of a Craft user.

**Be extra carefull** with this config because it can make your site easily hackable.
If the service you use for social login doesn't check the email of their customers, anyone could easily change their address to use the same one as one of your Craft users and get in straightaway.

Double check and double test all of the possible scenarios to make sure your social login process is as secure as possible.

**If you don't know what you're doing, please keep that setting set to `false`.**


### requireEmail

    'requireEmail' => true

- `true` — If the social service doesn't provider the customer's email address, the user is redirected to the `completeRegistrationTemplate` and the registration process doesn't complete until he provides a valid address.
- `false` — If the social service doesn't provider the customer's email address, a fake email address is being used for registration so that you can ask for their real email address later.

Most services will provide the customer's email address, but some of them, like Twitter, won't.

### completeRegistrationTemplate

    'completeRegistrationTemplate' => null,


## Templating


### Login

    <a href="{{ craft.social.loginUrl('google') }}">Login with Google</a>


### Logout

    <a href="{{ craft.social.logoutUrl() }}">Logout</a></li>