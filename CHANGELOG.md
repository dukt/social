Changelog
===================

## 2.0.0-beta.1 - Unreleased

### Added

- Added support section to composer.json

### Improved

- Improved Social variable for Craft 3.0.0 beta 20 compatibility
- The plugin now requires Craft 3.0.0 beta 20 or above
- Renamed changelog.md to CHANGELOG.md

### Craft 3.0 update

- `SocialController:_login()` is now taking a token instead of an account as a parameter
- `SocialUserIdentity::_construct()` is now taking an `Oauth_TokenModel` as a parameter instead of an account ID
- Users can now social login from the CP's login page
- Added an “Enable CP Login” plugin setting
- Fixed namespaces for `SocialUserIdentity`
- Admins can now remove Login Accounts from the CP user edit screen
- Users with access to the CP can now connect and disconnect login accounts from their account page
- Added a login accounts user table attribute
- Renamed `All login providers` to `All login accounts`
- Renamed `SocialTrait` to `RequirementsTrait`
- Renamed `ISocial_Provider` to `LoginProviderInterface`

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