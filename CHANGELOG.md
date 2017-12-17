Changelog
===================

## 2.0.0-beta.4 - Unreleased

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
- Fixed a bug where `\dukt\social\services\LoginAccounts::saveRemotePhoto()` was trying to remove a temp file that didn’t exist.

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