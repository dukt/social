Changelog
===================

## Unreleased

# Added
- OAuth provider credentials can now be saved from the control panel.
- Additional profile fields can now be requested when registering users with Facebook
- Added a `oauthProviders` config.
- Added `dukt\social\base\LoginProvider::getProfileFields()`.
- Added `dukt\social\base\LoginProvider::getUserMapping()`.
- Added `dukt\social\base\LoginProvider::getDefaultProfileFields()`.
- Added `dukt\social\base\LoginProvider::getDefaultUserMapping()`.
- Added `dukt\social\base\LoginProvider::getLoginProviderConfig()`.
- Added `dukt\social\Plugin::getLoginProviderConfig()`.

### Changed
- Login providers now return a specific OAuth 1 or 2 profile object instead of an array of data.
- OAuth scope, profile fields and user field mapping can now be customized for login providers using the `loginProviders` config.
- OAuth options, scope and authorization options can now be customized for OAuth providers using the `oauthProviders` config.
- Changed the `loginProviders` config.
- Don't delete the existing user photo before replacing it in `dukt\social\services\LoginAccounts::saveRemotePhoto()` since `craft\services\Users::saveUserPhoto()` already handles that.
- Removed `dukt\social\base\LoginProviderInterface::getProfile()`.
- Removed `dukt\social\base\LoginProvider::getRemoteProfile()`.
- Renamed `dukt\social\base\LoginProvider::getDefaultScope()` to `dukt\social\base\LoginProvider::getDefaultOauthScope()`.
- Renamed `dukt\social\base\LoginProvider::getScope()` to `dukt\social\base\LoginProvider::getOauthScope()`.
- Renamed `dukt\social\base\LoginProvider::getDefaultAuthorizationOptions()` to `dukt\social\base\LoginProvider::getDefaultOauthAuthorizationOptions()`.
- Renamed `dukt\social\base\LoginProvider::getAuthorizationOptions()` to `dukt\social\base\LoginProvider::getOauthAuthorizationOptions()`.
- Removed `dukt\social\services\LoginProviders::getUserMapping()`.

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