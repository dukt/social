<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\services;

use Craft;
use yii\base\Component;
use dukt\social\base\RequirementsTrait;
use craft\helpers\UrlHelper;

class Social extends Component
{
    // Traits
    // =========================================================================

    use RequirementsTrait;

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

		$url = UrlHelper::siteUrl(Craft::$app->config->get('actionTrigger').'/social/social/login', $params);

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

		return UrlHelper::actionUrl('social/social/logout', $params);
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
		return UrlHelper::actionUrl('social/social/connect-login-account', [
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
		return UrlHelper::actionUrl('social/social/disconnect-login-account', [
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

		$tempPath = Craft::$app->path->getTempPath().'social/userphotos/'.$user->email.'/';
		IOHelper::createFolder($tempPath);
		$tempFilepath = $tempPath.$filename;
		$client = new \Guzzle\Http\Client();
		$response = $client->get($photoUrl)
			->setResponseBody($tempPath.$filename)
			->send();

		$extension = substr($response->getContentType(), strpos($response->getContentType(), "/") + 1);

		IOHelper::rename($tempPath.$filename, $tempPath.$filename.'.'.$extension);

		Craft::$app->users->deleteUserPhoto($user);

		$image = Craft::$app->images->loadImage($tempPath.$filename.'.'.$extension);
		$imageWidth = $image->getWidth();
		$imageHeight = $image->getHeight();

		$dimension = min($imageWidth, $imageHeight);
		$horizontalMargin = ($imageWidth - $dimension) / 2;
		$verticalMargin = ($imageHeight - $dimension) / 2;
		$image->crop($horizontalMargin, $imageWidth - $horizontalMargin, $verticalMargin, $imageHeight - $verticalMargin);

		Craft::$app->users->saveUserPhoto($filename.'.'.$extension, $image, $user);

		IOHelper::deleteFile($tempPath.$filename.'.'.$extension);

		return true;
	}
}
