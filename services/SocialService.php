<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'social/base/SocialTrait.php');

class SocialService extends BaseApplicationComponent
{

    // Traits
    // =========================================================================

    use SocialTrait;

	// Public Methods
	// =========================================================================

	/**
	 * Get login URL
	 *
	 * @param $providerHandle
	 * @param array  $params
	 *
	 * @return string
	 */
	public function getLoginUrl($providerHandle, array $params = [])
	{
		$params['provider'] = $providerHandle;

		if (isset($params['scope']) && is_array($params['scope']))
		{
			$params['scope'] = urlencode(base64_encode(serialize($params['scope'])));
		}

		$url = UrlHelper::getSiteUrl(craft()->config->get('actionTrigger').'/social/login', $params);

		return $url;
	}

	/**
	 * Get logout URL
	 *
	 * @param string|null $redirect
	 *
	 * @return string
	 */
	public function getLogoutUrl($redirect = null)
	{
		$params = ['redirect' => $redirect];

		return UrlHelper::getActionUrl('social/logout', $params);
	}

	/**
	 * Get link account URL
	 *
	 * @param $handle
	 *
	 * @return string
	 */
	public function getLoginAccountConnectUrl($handle)
	{
		return UrlHelper::getActionUrl('social/connectLoginAccount', [
			'provider' => $handle
		]);
	}

	/**
	 * Get unlink account URL
	 *
	 * @param $handle
	 *
	 * @return string
	 */
	public function getLoginAccountDisconnectUrl($handle)
	{
		return UrlHelper::getActionUrl('social/disconnectLoginAccount', [
			'provider' => $handle
		]);
	}

	/**
	 * Save remote photo
	 *
	 * @param string $photoUrl
	 * @param UserModel $user
	 *
	 * @return bool
	 */
	public function saveRemotePhoto($photoUrl, UserModel $user)
	{
		$filename = 'photo';

		$tempPath = craft()->path->getTempPath().'social/userphotos/'.$user->email.'/';
		IOHelper::createFolder($tempPath);
		$tempFilepath = $tempPath.$filename;
		$client = new \Guzzle\Http\Client();
		$response = $client->get($photoUrl)
			->setResponseBody($tempPath.$filename)
			->send();

		$extension = substr($response->getContentType(), strpos($response->getContentType(), "/") + 1);

		IOHelper::rename($tempPath.$filename, $tempPath.$filename.'.'.$extension);

		craft()->users->deleteUserPhoto($user);

		$image = craft()->images->loadImage($tempPath.$filename.'.'.$extension);
		$imageWidth = $image->getWidth();
		$imageHeight = $image->getHeight();

		$dimension = min($imageWidth, $imageHeight);
		$horizontalMargin = ($imageWidth - $dimension) / 2;
		$verticalMargin = ($imageHeight - $dimension) / 2;
		$image->crop($horizontalMargin, $imageWidth - $horizontalMargin, $verticalMargin, $imageHeight - $verticalMargin);

		craft()->users->saveUserPhoto($filename.'.'.$extension, $image, $user);

		IOHelper::deleteFile($tempPath.$filename.'.'.$extension);

		return true;
	}
}
