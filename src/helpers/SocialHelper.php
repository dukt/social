<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\helpers;

use Craft;
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
     * @param string            $path
     * @param array|string|null $params
     * @param string|null       $protocol The protocol to use (e.g. http, https). If empty, the protocol used for the current
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
        $redirectUri = str_replace(Craft::$app->getConfig()->getGeneral()->cpTrigger.'/', '', $redirectUri);

        return $redirectUri;
    }
}
