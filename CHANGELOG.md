Changelog
===================

## 2.0.0-beta.1 - Unreleased

### Added
- Craft 3.0 compatiblity.
- Users can now social login from the CP's login page.
- Added an “Enable CP Login” plugin setting.
- Admins can now remove Login Accounts from the CP user edit screen.
- Users with access to the CP can now connect and disconnect login accounts from their account page.
- Added a login accounts user table attribute.
- Added `dukt\social\base\PluginTrait`.
- Added `dukt\social\elements\db\LoginAccountQuery`.
- Added `dukt\social\models\Settings`.
- Added `dukt\social\models\Token`.
- Added `dukt\social\web\assets\login\LoginAsset`.
- Added `dukt\social\web\assets\loginaccountindex\LoginAccountIndexAsset`.
- Added `dukt\social\web\assets\social\SocialAsset`.
- Added `_components/users/login-accounts-pane.html` template.
- Added `_components/users/login-accounts-table-attribute.html` template.
- Added `loginaccounts/_element.html` template.
- Added `settings/_general.html` template.

### Changed
- Removed `Craft\Social_InstallController`.
- Removed `Craft\Social_LoginAccountModel`.
- Removed `Craft\Social_ProviderModel`.
- Removed `Craft\Social_UserSessionService`.
- Removed `Craft\SocialController`.
- Removed `Craft\SocialService`.
- Removed `Craft\SocialTrait`.
- Removed `Craft\SocialUserIdentity`.
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
- Renamed `settings/_index.html` template to `settings/index.html`.

## 1.2.4 - 2017-01-13

### Added
- Added `SocialTrait`
- Added `Social_InstallController`
- Added `docsUrl` to settings pages

### Improved
- Improved installation process
- Checking plugin requirements from `Social_ProviderModel::getOauthProvider()` and `Social_LoginAccountModel::getOauthProvider()`
- Removed `Social_PluginController`
- Removed `Social_PluginService`