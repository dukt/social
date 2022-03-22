<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2021, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\helpers;

use Craft;
use craft\elements\User;
use craft\helpers\UrlHelper;

/**
 * Class SocialHelper
 *
 * @author Dukt <support@dukt.net>
 * @since  1.0
 */
class SocialHelper
{
    // Public Methods
    // =========================================================================

    /**
     * Returns a site action URL.
     *
     * @param string $path
     * @param array|string|null $params
     * @param string|null $protocol The protocol to use (e.g. http, https). If empty, the protocol used for the current
     *                                    request will be used.
     *
     * @return string
     */
    public static function siteActionUrl(string $path = '', $params = null, string $protocol = null): string
    {
        // Force `addTrailingSlashesToUrls` to `false` while we generate the redirectUri
        $addTrailingSlashesToUrls = Craft::$app->getConfig()->getGeneral()->addTrailingSlashesToUrls;
        Craft::$app->getConfig()->getGeneral()->addTrailingSlashesToUrls = false;

        $redirectUri = UrlHelper::actionUrl($path, $params, $protocol);

        // Set `addTrailingSlashesToUrls` back to its original value
        Craft::$app->getConfig()->getGeneral()->addTrailingSlashesToUrls = $addTrailingSlashesToUrls;

        // We don't want the CP trigger showing in the action URL.
        $redirectUri = str_replace(Craft::$app->getConfig()->getGeneral()->cpTrigger . '/', '', $redirectUri);

        return $redirectUri;
    }

    /**
     * @param User $newUser
     * @param      $attribute
     * @param      $template
     * @param      $profile
     */
    public static function fillUserAttribute(User $newUser, $attribute, $template, $profile)
    {
        if (array_key_exists($attribute, $newUser->getAttributes())) {
            try {
                $newUser->{$attribute} = Craft::$app->getView()->renderString($template, ['profile' => $profile]);
            } catch (\Exception $exception) {
                Craft::warning('Could not map:' . print_r([$attribute, $template, $profile, $exception->getMessage()], true), __METHOD__);
            }
        }
    }

    /**
     * @param User $newUser
     * @param       $attribute
     * @param       $template
     * @param       $profile
     */
    public static function fillUserCustomFieldValue(User $newUser, $attribute, $template, $profile)
    {
        // Check to make sure custom field exists for user profile
        if (isset($newUser->{$attribute})) {
            try {
                $value = Craft::$app->getView()->renderString($template, ['profile' => $profile]);
                $newUser->setFieldValue($attribute, $value);
            } catch (\Exception $exception) {
                Craft::warning('Could not map:' . print_r([$template, $profile, $exception->getMessage()], true), __METHOD__);
            }
        }
    }
}
