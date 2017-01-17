Social Changelog
===================

## Unreleased

- SocialController:_login() is now taking a token instead of an account as a parameter
- SocialUserIdentity::_construct() is now taking an Oauth_TokenModel as a parameter instead of an account ID
- Users can now social login from the CP's login page
- Added an “Enable CP Login” plugin setting
- Fixed namespaces for `SocialUserIdentity`

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