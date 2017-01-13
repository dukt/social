<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class SocialPlugin extends BasePlugin
{
	// Public Methods
	// =========================================================================

	/**
	 * Initialization
	 */
	public function init()
	{
		require_once(CRAFT_PLUGINS_PATH.'social/etc/providers/ISocial_Provider.php');
		require_once(CRAFT_PLUGINS_PATH.'social/providers/login/BaseProvider.php');

		$this->initEventListeners();
	}

	/**
	 * Get Social Login Providers
	 */
	public function getSocialLoginProviders()
	{
		require_once(CRAFT_PLUGINS_PATH.'social/providers/login/Facebook.php');
		require_once(CRAFT_PLUGINS_PATH.'social/providers/login/Google.php');
		require_once(CRAFT_PLUGINS_PATH.'social/providers/login/Twitter.php');

		return [
			'Dukt\Social\LoginProviders\Facebook',
			'Dukt\Social\LoginProviders\Google',
			'Dukt\Social\LoginProviders\Twitter',
		];
	}

	/**
	 * Get Required Dependencies
	 */
	public function getRequiredPlugins()
	{
		return [
			[
				'name'    => "OAuth",
				'handle'  => 'oauth',
				'url'     => 'https://dukt.net/craft/oauth',
				'version' => '2.0.2'
			]
		];
	}

	/**
	 * Get Name
	 */
	public function getName()
	{
		return Craft::t('Social Login');
	}

	/**
	 * Get Description
	 */
	public function getDescription()
	{
		return Craft::t('Let your visitors log into Craft with web services like Facebook, Google, Twitter…');
	}

	/**
	 * Get Version
	 */
	public function getVersion()
	{
		return '1.2.4';
	}


	/**
	 * Get SchemaVersion
	 */
	public function getSchemaVersion()
	{
		return '1.0.2';
	}

	/**
	 * Get Developer
	 */
	public function getDeveloper()
	{
		return 'Dukt';
	}

	/**
	 * Get Developer URL
	 */
	public function getDeveloperUrl()
	{
		return 'https://dukt.net/';
	}

	/**
	 * Get Documentation URL
	 */
	public function getDocumentationUrl()
	{
		return 'https://dukt.net/craft/social/docs/';
	}

	/**
	 * Get Release Feed URL
	 */
	public function getReleaseFeedUrl()
	{
		return 'https://dukt.net/craft/social/updates.json';
	}

	/**
	 * Get Settings URL
	 */
	public function getSettingsUrl()
	{
		return 'settings/plugins/social/settings/loginproviders';
	}

	/**
	 * Has CP Section
	 */
	public function hasCpSection()
	{
		$socialPlugin = craft()->plugins->getPlugin('social');
		$settings = $socialPlugin->getSettings();

		if ($settings['showCpSection'])
		{
			return true;
		}

		return false;
	}

	/**
	 * Hook Register CP Routes
	 */
	public function registerCpRoutes()
	{
		return [
			"social" => ['action' => "social/loginAccounts/index"],

			'social/install' => ['action' => "social/install/index"],
			'social/settings' => ['action' => "social/settings/index"],

			"social/loginaccounts" => ['action' => "social/loginAccounts/index"],
			"social/loginaccounts/(?P<userId>\d+)" => ['action' => "social/loginAccounts/edit"],

			'settings/plugins/social/settings/loginproviders' => ['action' => "social/loginProviders/index"],
			'settings/plugins/social/settings/loginproviders/(?P<handle>.*)' => ['action' => "social/loginProviders/edit"],

			'settings/plugins/social/settings/settings' => ['action' => "social/settings/index"],
		];
	}

	// Protected Methods
	// =========================================================================

	/**
	 * Define Settings
	 */
	protected function defineSettings()
	{
		return [
			'enableSocialRegistration' => [AttributeType::Bool, 'default' => true],
			'enableSocialLogin' => [AttributeType::Bool, 'default' => true],
			'loginProviders' => [AttributeType::Mixed],
			'defaultGroup' => [AttributeType::Number, 'default' => null],
			'autoFillProfile' => [AttributeType::Bool, 'default' => true],
			'showCpSection' => [AttributeType::Bool, 'default' => true],
		];
	}

	// Private Methods
	// =========================================================================

	/**
	 * Initialize event listeners
	 */
	private function initEventListeners()
	{
		// delete social user when craft user is deleted

		craft()->on('users.onBeforeDeleteUser', function (Event $event)
		{
			$user = $event->params['user'];

			craft()->social_loginAccounts->deleteLoginAccountByUserId($user->id);
		});
	}
}
