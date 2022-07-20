# Updating Social

When an update is available, users with the permission to perform updates will see a badge in the CP next to the Utilities navigation item in the sidebar. Click on Utilities and then choose Updates. You can also use the Updates widget on the Control Panel dashboard, which is installed by default.

This section displays both Craft CMS updates and plugin updates. Click the Update button for Social to initiate the self-updating process.

You can run all of the updates (Craft, all plugin updates available) using the Update All button at the top left of the Updates page.

## Changes in Social 2.0.0-beta.10

- The [loginProviders](configuration.md#loginproviders) config has changed and now lets you define the user field mapping, the profile fields and the OAuth configuration for login providers. If you were using that config to set a login provider’s client ID and secret, make sure to update the `loginProviders` config array.