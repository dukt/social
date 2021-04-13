Changelog
===================

## Unreleased

### Added
- Added environment variable suggestions support for the OAuth client ID and secret.
- The OAuth settings page for the Google login provider now show the JavaScript origin URL.  

## 2.0.2 - 2021-03-04

### Changed
- Updated `league/oauth1-client` to 1.9.
- Updated `league/oauth2-client` to 2.5.
- Check the OAuth state to mitigate CSRF attacks.
- Improved environment variables and project config support.

### Fixed
- Fixed a bug where checking for locked domains could throw an error.
- Fixed a bug where the social login buttons were not properly centered on the CP login page.
- Fixed a compatibility issue with Guzzle 7 that could prevent the user image from being saved when registering with a Social login provider.
- Fixed a bug where notices and errors were not working properly when logging into the Control Panel.
- Fixed the padding of the login account manager pane on the user edit page.
- Fixed the styles of the plugin’s settings.

## 2.0.1 - 2019-05-20

### Added
- Added a warning for Twitter redirect URIs containing unsupported query parameters.

### Changed
- Social now requires Craft CMS 3.1+. ([#31](https://github.com/dukt/social/issues/31))

### Fixed
- Fixed a bug where Social was not handling soft deletes properly, preventing users from registering with the same social credentials as a soft-deleted user. ([#32](https://github.com/dukt/social/issues/32), [#35](https://github.com/dukt/social/issues/35))

## 2.0.0 - 2019-03-04

### Added
- Added `dukt\social\controllers\LoginAccountsController::EVENT_AFTER_OAUTH_CALLBACK`. ([#22](https://github.com/dukt/social/issues/22))

### Changed
- Removed `dukt\social\services\LoginAccounts::saveLoginAccount()`. ([#26](https://github.com/dukt/social/issues/26))

### Fixed
- Fixed a bug where registering a user with a social account that was used for a soft-deleted user was throwing an error, preventing completion of the registration process. ([#6](https://github.com/dukt/social/issues/6))

## 2.0.0-beta.16 - 2019-03-03

### Changed
- Replaced `dukt/oauth2-google` composer dependency with `league/oauth2-google`.

## 2.0.0-beta.15 - 2019-03-01

### Added
- Added `dukt\social\controllers\LoginAccountsController::EVENT_AFTER_REGISTER` event handler after user registration. ([#25](https://github.com/dukt/social/pull/25), [#23](https://github.com/dukt/social/issues/23))

### Fixed
- Fixed a bug where the login accounts user pane was showing up when trying to create a new user. ([#28](https://github.com/dukt/social/issues/28))
- Fixed a bug where the `dukt\social\controllers\LoginAccountsController::EVENT_BEFORE_REGISTER` was not working properly. ([#25](https://github.com/dukt/social/pull/25))

## 2.0.0-beta.14 - 2018-09-10

### Added
- Added missing Craft 3 upgrade migration.

### Fixed
- Fixed a bug where profile couldn’t be filled properly on user registration.
- Fixed bug where too many arguments were passed to `\dukt\social\services\LoginProviders::getLoginProvider()` in `\dukt\social\controllers\LoginProvidersController::actionOauth()`.

## 2.0.0-beta.13 - 2018-08-29

### Fixed
- Fixed a bug that prevented domain locking from working properly. ([#19](https://github.com/dukt/social/issues/19))

## 2.0.0-beta.12 - 2018-08-25

### Changed
- Deleting a a login account now requires admin privileges.

### Fixed
- Fixed a bug that prevented a user’s login provider from getting disconnected.

## 2.0.0-beta.11 - 2018-08-24

### Fixed
- Fixed a bug where client ID and secret were not properly saved when saving them from the control panel. ([#11](https://github.com/dukt/social/issues/11))
- Fixed a translation bug when saving a login provider's OAuth configuration. ([#14](https://github.com/dukt/social/pull/14))

## 2.0.0-beta.10 - 2018-06-29

### Added
- Additional profile fields can now be requested when registering users with Facebook.
- OAuth provider credentials can now be saved from the control panel.
- Added `dukt\social\base\LoginProvider::getDefaultProfileFields()`.
- Added `dukt\social\base\LoginProvider::getLoginProviderConfig()`.
- Added `dukt\social\base\LoginProvider::getProfileFields()`.
- Added `dukt\social\base\LoginProvider::getUserFieldMapping()`.
- Added `dukt\social\base\LoginProviderInterface::getDefaultUserFieldMapping()`.
- Added `dukt\social\base\LoginProviderInterface::getOauthProvider()`.
- Added `dukt\social\Plugin::getLoginProviderConfig()`.

### Changed
- The `loginProviders` config now defines the user field mapping, the profile fields and the OAuth configuration for login providers.
- Don't delete the existing user photo before replacing it in `dukt\social\services\LoginAccounts::saveRemotePhoto()` since `craft\services\Users::saveUserPhoto()` already handles that.
- Login providers now return a specific OAuth 1 or 2 profile object instead of an array of data.
- User field mapping, profile fields and OAuth scope can now be customized using the `loginProviders` config.
- Updated Facebook Graph API version to `v3.0`.
- Removed `dukt\social\base\LoginProvider::getRemoteProfile()`.
- Removed `dukt\social\base\LoginProviderInterface::getProfile()`.
- Removed `dukt\social\models\Settings::$showCpSection`.
- Removed `dukt\social\Plugin::$plugin`.
- Removed `dukt\social\services\LoginProviders::getUserMapping()`.
- Removed `dukt\social\Plugin::beforeUpdate()`.
- Renamed `dukt\social\base\LoginProvider::getAuthorizationOptions()` to `dukt\social\base\LoginProvider::getOauthAuthorizationOptions()`.
- Renamed `dukt\social\base\LoginProvider::getDefaultAuthorizationOptions()` to `dukt\social\base\LoginProvider::getDefaultOauthAuthorizationOptions()`.
- Renamed `dukt\social\base\LoginProvider::getDefaultScope()` to `dukt\social\base\LoginProvider::getDefaultOauthScope()`.
- Renamed `dukt\social\base\LoginProvider::getScope()` to `dukt\social\base\LoginProvider::getOauthScope()`.

### Fixed
- Fixed a bug where Social 1.1.0 was not required to update to Social 2.0+.

## 2.0.0-beta.9 - 2018-05-24

### Added
- User mapping configuration is now displayed on login providers’ settings page.

### Changed
- Reworked the way user fields are being mapped and filled on registration.
- Removed unused `dukt\social\controllers\LoginAccountsController::actionChangePhoto()` method.

## 2.0.0-beta.8 - 2018-05-18

### Added
- Added `dukt\social\models\Settings::$loginProviders` property.

### Changed
- Typed `dukt\social\base\LoginProviderInterface::getName()`’s return to string.

## 2.0.0-beta.7 - 2018-05-08

### Changed
- Improved login and registration exception handling.
- Email matching social registration is not subject to domain locking anymore.
- Require Pro edition only when registering a user.

## 2.0.0-beta.6 - 2018-04-25

### Added
- Facebook’s Graph API version can now be configured with a `graphApiVersion` login provider config.

### Changed 
- Updated default Graph API version to v2.12.

### Fixed
- Fixed a bug where new users couldn’t be assigned to the default user group defined in the plugin’s settings.
- Fixed a bug where table prefix was not taken into account for LoginAccount records.
- Fixed a bug where CP Social Login could be initialized before third party plugin had a chance to register login providers.

## 2.0.0-beta.5 - 2017-12-17

### Improved
- Make sure the social UID is a string when authenticating.

## 2.0.0-beta.4 - 2017-12-17

### Added
- Added Craft license.

### Changed
- Updated the plugin’s icon.
- Updated to require craftcms/cms `^3.0.0-RC1`.

### Fixed
- Fixed a bug that would make the Users edit page crash when user is connected to a disabled or uninstalled login providers.
- Fixed login buttons on the login form.
- Fixed login accounts pane’s styles on the Users edit page.

## 2.0.0-beta.3 - 2017-10-09

### Fixed
- Fixed a bug where the redirect URL was not properly encoded for Facebook.
- Fixed a bug where Facebook’s OAuth token couldn’t be retrieved.

## 2.0.0-beta.2 - 2017-09-24

### Added
- Added the `registerLoginProviderTypes` event to `dukt\social\services\LoginAccounts`, giving plugins a change to register login provider types (replacing `getSocialLoginProviders()`).
- Added `dukt\social\events\RegisterLoginProviderTypesEvent`.

### Fixed
- Fixed a bug where login providers were not sorted alphabetically.
- Fixed with the setting pages’ doc title.

### Improved
- Improved instructions for login provider settings. 
- Now using the `craft\web\twig\variables\CraftVariable`’s `init` event to register Social’s variable class, replacing the now-deprecated `defineComponents`.
- Removed `dukt\social\Plugin::getSocialLoginProviders()`.
- Replaced tabs with nav for the plugin’s settings navigation.
- Renamed “General” settings nav item to “Settings“.


## 2.0.0-beta.1 - 2017-09-06

### Added
- Craft 3.0 compatibility.
- Users can now social login from the CP's login page.
- Added an “Enable CP Login” plugin setting.
- Admins can now remove Login Accounts from the CP user edit screen.
- Users with access to the CP can now connect and disconnect login accounts from their account page.
- Added a login accounts user table attribute.
- Social 1.1.0 is required before updating to Craft 3.
- Added `dukt\social\base\LoginProvider::__toString()`.
- Added `dukt\social\base\LoginProvider::getInfos()`.
- Added `dukt\social\base\LoginProvider::getManagerUrl()`.
- Added `dukt\social\base\LoginProvider::getRedirectUri()`.
- Added `dukt\social\base\LoginProvider::getScopeDocsUrl()`.
- Added `dukt\social\base\PluginTrait`.
- Added `dukt\social\elements\db\LoginAccountQuery`.
- Added `dukt\social\elements\LoginAccount::defineDefaultTableAttributes()`.
- Added `dukt\social\elements\LoginAccount::getUsername()`.
- Added `dukt\social\elements\LoginAccount::getFirstName()`.
- Added `dukt\social\elements\LoginAccount::getLastName()`.
- Added `dukt\social\elements\LoginAccount::getEmail()`.
- Added `dukt\social\errors\LoginAccountNotFoundException`.
- Added `dukt\social\loginproviders\Facebook::getManagerUrl()`.
- Added `dukt\social\loginproviders\Facebook::getScopeDocsUrl()`.
- Added `dukt\social\loginproviders\Google::getManagerUrl()`.
- Added `dukt\social\loginproviders\Google::getScopeDocsUrl()`.
- Added `dukt\social\loginproviders\Twitter::getManagerUrl()`.
- Added `dukt\social\models\Settings`.
- Added `dukt\social\models\Token`.
- Added `dukt\social\web\assets\login\LoginAsset`.
- Added `dukt\social\web\assets\loginaccountindex\LoginAccountIndexAsset`.
- Added `dukt\social\web\assets\social\SocialAsset`.
- Added `_components/users/login-accounts-pane.html` template.
- Added `_components/users/login-accounts-table-attribute.html` template.
- Added `loginaccounts/_element.html` template.
- Added `settings/_general.html` template.
- Added `icons/facebook.svg` icon.
- Added `icons/google.svg` icon.
- Added `icons/twitter.svg` icon.
- Added `craftcms/cms:^3.0.0-beta.20` dependency.
- Added `league/oauth1-client:1.7.0@dev` dependency.
- Added `league/oauth2-client:^2.2` dependency.
- Added `dukt/oauth2-google:^2.0"` dependency.
- Added `league/oauth2-facebook:^2.0` dependency.

### Improved
- Login providers now appear disabled if the OAuth client ID is not set.
- `dukt\social\elements\LoginAccount::authenticate()` now checks that there is a matching Social UID before logging in.
- Using echos instead of Craft logs for migrations.
- The plugin doesn’t require the OAuth plugin for Craft anymore.
- Login accounts attached to a user are being saved again after saving a user in order to update the search index.
- `dukt\social\base\LoginProvider::getRemoteProfile()` is now a protected method.
- Removed support for `scope` parameter in `dukt\social\services\LoginAccounts::getLoginUrl()`.
- Removed `advancedMode` config setting.
- Removed `Craft\Social_InstallController`.
- Removed `Craft\Social_LoginAccountModel`.
- Removed `Craft\Social_ProviderModel`.
- Removed `Craft\Social_UserSessionService`.
- Removed `Craft\SocialController`.
- Removed `Craft\SocialService`.
- Removed `Craft\SocialTrait`.
- Removed `Craft\SocialUserIdentity`.
- Removed `dukt\social\base\LoginProvider::getOauthProvider()`.
- Removed `dukt\social\controllers\LoginAccountsController::actionLogout()`.
- Removed `dukt\social\services\LoginAccounts::getLogoutUrl()`.
- Removed `dukt\social\web\twig\variables\SocialVariable::getLogoutUrl()`.
- Removed `_special/install/dependencies.html` template.
- Removed `_special/install/dependencies.html` template.
- Renamed `Craft\Social_LoginAccountElementType` to `dukt\social\elements\LoginAccount` .
- Renamed `Craft\Social_LoginAccountRecord` to `dukt\social\records\LoginAccount`.
- Renamed `Craft\Social_LoginAccountsController` to `dukt\social\controllers\LoginAccountsController`.
- Renamed `Craft\Social_LoginAccountsService` to `dukt\social\services\LoginAccounts`.
- Renamed `Craft\Social_LoginProvidersController` to `dukt\social\controllers\LoginProvidersController`.
- Renamed `Craft\Social_LoginProvidersService` to `dukt\social\services\LoginProviders`.
- Renamed `Craft\Social_SettingsController` to `dukt\social\controllers\SettingsController`.
- Renamed `Craft\SocialPlugin` to `dukt\social\Plugin`.
- Renamed `Craft\SocialVariable` to `dukt\social\web\twig\variables\SocialVariable`.
- Renamed `Dukt\Social\LoginProviders\BaseProvider` to `dukt\social\base\LoginProvider`.
- Renamed `$referer` to `$originUrl` in `dukt\social\controllers\LoginAccountsController`.
- Renamed `settings/_index.html` template to `settings/index.html`.
- Renamed `resources/images/defaultuser.svg` to `icons/defaultuser.svg`.

### Fixed
- Fixed a bug where `dukt\social\services\LoginAccounts::saveRemotePhoto()` was trying to remove a temp file that didn’t exist.

## 1.2.4 - 2017-01-13

### Added
- Added `SocialTrait`.
- Added `Social_InstallController`.
- Added `docsUrl` to settings pages.

### Improved
- Improved installation process.
- Checking plugin requirements from `Social_ProviderModel::getOauthProvider()` and `Social_LoginAccountModel::getOauthProvider()`.
- Removed `Social_PluginController`.
- Removed `Social_PluginService`.