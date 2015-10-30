<?php

/**
 * Social plugin for Craft
 *
 * @package   Craft Social
 * @author    Benjamin David
 * @copyright Copyright (c) 2015, Dukt
 * @link      https://dukt.net/craft/social/
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace Craft;

class SocialService extends BaseApplicationComponent
{
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

		if (isset($params['scopes']) && is_array($params['scopes']))
		{
			$params['scopes'] = urlencode(base64_encode(serialize($params['scopes'])));
		}

		$url = UrlHelper::getSiteUrl(craft()->config->get('actionTrigger').'/social/login', $params);

		Craft::log(__METHOD__." : Authenticate : ".$url, LogLevel::Info, true);

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
	public function getLinkAccountUrl($handle)
	{
		return UrlHelper::getActionUrl('social/link', [
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
	public function getUnlinkAccountUrl($handle)
	{
		return UrlHelper::getActionUrl('social/unlink', [
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
