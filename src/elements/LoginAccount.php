<?php
/**
 * @link      https://dukt.net/craft/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/social/docs/license
 */

namespace dukt\social\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use dukt\social\elements\db\LoginAccountQuery;
use dukt\social\web\assets\loginaccountindex\LoginAccountIndexAsset;
use yii\web\IdentityInterface;
use craft\helpers\UrlHelper;
use dukt\social\Plugin as Social;

/**
 * Class LoginAccount
 *
 * @package dukt\social\elements
 */
class LoginAccount extends Element implements IdentityInterface
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    private $_user;

    /**
     * @var
     */
    public $userId;

    /**
     * @var
     */
    public $providerHandle;

    /**
     * @var
     */
    public $socialUid;

    /**
     * @var
     */
    public $username;

    /**
     * @var
     */
    public $email;

    /**
     * @var
     */
    public $firstName;

    /**
     * @var
     */
    public $lastName;

    /**
     * @var
     */
    public $lastLoginDate;

    // Public Methods
    // =========================================================================

    /**
     * Use the login account's email or username as its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        if (Craft::$app->getConfig()->get('useEmailAsUsername'))
        {
            return (string) $this->email;
        }
        else
        {
            return (string) $this->username;
        }
    }

    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return true;
    }

    /**
     * @param int $size
     *
     * @return mixed
     */
    public function getThumbUrl(int $size = 100)
    {
        $asset = $this->getUser()->getPhoto();

        if($asset)
        {
            $url = $asset->getThumbUrl($size);

            if (!$url)
            {
                $url = UrlHelper::getResourceUrl('defaultuserphoto');
            }

            return $url;
        }
    }

    /**
     * Returns the element's CP edit URL.
     *
     * @return string|false
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl('social/loginaccounts/'.$this->userId);
    }

    /**
     * @return ElementQueryInterface
     */
    public static function find(): ElementQueryInterface
    {
        return new LoginAccountQuery(get_called_class());
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        $firstName = trim($this->firstName);
        $lastName = trim($this->lastName);

        return $firstName.($firstName && $lastName ? ' ' : '').$lastName;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        if (!isset($this->_user))
        {
            if ($this->userId)
            {
                $this->_user = Craft::$app->users->getUserById($this->userId);
            }
        }

        return $this->_user;
    }

    /**
     * Returns the element type name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('app', 'Login Accounts');
    }

    /**
     * Returns the element index HTML.
     *
     * @param ElementCriteriaModel $criteria
     * @param array                $disabledElementIds
     * @param array                $viewState
     * @param string|null          $sourceKey
     * @param string|null          $context
     * @param bool                 $includeContainer
     * @param bool                 $showCheckboxes
     *
     * @return string
     */
    public function getIndexHtml($criteria, $disabledElementIds, $viewState, $sourceKey, $context, $includeContainer, $showCheckboxes)
    {
        Craft::$app->getView()->registerAssetBundle(LoginAccountIndexAsset::class);

        return parent::getIndexHtml($criteria, $disabledElementIds, $viewState, $sourceKey, $context, $includeContainer, $showCheckboxes);
    }

    public function getOauthProvider()
    {
        if ($this->providerHandle)
        {
            $loginProvider = $this->getLoginProvider();

            if($loginProvider)
            {
                return $loginProvider->getOauthProvider();
            }
        }
    }

    public function getLoginProvider()
    {
        if ($this->providerHandle)
        {
            return Social::$plugin->getLoginProviders()->getLoginProvider($this->providerHandle);
        }
    }

    /**
     * Returns this element type's sources.
     *
     * @param string|null $context
     *
     * @return array|false
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = array(
            array(
                'key' => '*',
                'label' => Craft::t('app', 'All login accounts'),
                'criteria' => [],
                'hasThumbs' => false
            )
        );

        $loginProviders = Social::$plugin->getLoginProviders()->getLoginProviders();

        if ($loginProviders)
        {
            $sources[] = array('heading' => Craft::t('app', 'Login Providers'));

            foreach ($loginProviders as $loginProvider)
            {
                $providerHandle = $loginProvider->getHandle();
                $key = 'group:'.$providerHandle;

                $sources[] = array(
                    'key' => $key,
                    'label'     => Craft::t('app', $loginProvider->getName()),
                    'criteria'  => array('providerHandle' => $providerHandle),
                    'hasThumbs' => false
                );
            }
        }

        // Allow plugins to modify the sources
        /*Craft::$app->getPlugins()->call('modifyLoginAccountSources', array(&$sources, $context));*/

        return $sources;
    }

    /**
     * Returns the available element actions for a given source (if one is provided).
     *
     * @param string|null $source
     *
     * @return array|null
     */
    public function getAvailableActions($source = null)
    {
        $actions = array();

        $deleteAction = Craft::$app->elements->getAction('Delete');
        $deleteAction->setParams(array(
            'confirmationMessage' => Craft::t('app', 'Are you sure you want to delete the selected login accounts?'),
            'successMessage'      => Craft::t('app', 'Login accounts deleted.'),
        ));
        $actions[] = $deleteAction;

        // Allow plugins to add additional actions
        $allPluginActions = Craft::$app->getPlugins()->call('addLoginAccountActions', array($source), true);

        foreach ($allPluginActions as $pluginActions)
        {
            $actions = array_merge($actions, $pluginActions);
        }

        return $actions;
    }

    /**
     * Defines which element model attributes should be searchable.
     *
     * @return array
     */
    protected static function defineSearchableAttributes(): array
    {
        return array('username', 'email', 'firstName', 'lastName', 'fullName', 'providerHandle', 'socialUid', 'userId');
    }

    /**
     * Defines the attributes that elements can be sorted by.
     *
     * @return array
     */
    protected static function defineSortOptions(): array
    {
        if (Craft::$app->getConfig()->get('useEmailAsUsername'))
        {
            // Start with Email and don't even give Username as an option
            $attributes = array(
                'email' => Craft::t('app', 'Email'),
            );
        }
        else
        {
            $attributes = array(
                'username' => Craft::t('app', 'Username'),
                'email'    => Craft::t('app', 'Email'),
            );
        }

        $attributes['firstName']     = Craft::t('app', 'First Name');
        $attributes['lastName']      = Craft::t('app', 'Last Name');

        $attributes['providerHandle'] = Craft::t('app', 'Login Provider');
        $attributes['socialUid']     = Craft::t('app', 'Social User ID');

        $attributes['userId']        = Craft::t('app', 'User ID');
        $attributes['lastLoginDate'] = Craft::t('app', 'Last Login');
        $attributes['dateCreated']   = Craft::t('app', 'Date Created');
        $attributes['dateUpdated']   = Craft::t('app', 'Date Updated');

        // Allow plugins to modify the attributes
        // Craft::$app->getPlugins()->call('modifyLoginAccountSortableAttributes', array(&$attributes));

        return $attributes;
    }

    /**
     * Returns the HTML that should be shown for a given element’s attribute in Table View.
     *
     * @param BaseElementModel $element
     * @param string           $attribute
     *
     * @return string
     */
    public function tableAttributeHtml(string $attribute): string
    {
        // First give plugins a chance to set this

        /*
        $pluginAttributeHtml = Craft::$app->getPlugins()->callFirst('getLoginAccountTableAttributeHtml', array($element, $attribute), true);

        if ($pluginAttributeHtml !== null) {
            return $pluginAttributeHtml;
        }
        */

        switch ($attribute)
        {
            case 'providerHandle':
            {
                $provider = Social::$plugin->getLoginProviders()->getLoginProvider($this->providerHandle);

                if ($provider)
                {
                    $html = '<div class="provider">' .
                        '<div class="thumb"><img src="'.$provider->getIconUrl().'" width="32" height="32" /></div>' .
                        '<div class="label">'.$provider->getName().'</div>' .
                        '</div>';

                    return $html;
                }
                else
                {
                    return '';
                }
            }

            default:
            {
                return parent::tableAttributeHtml($attribute);
            }
        }
    }

    /**
     * @param $token
     *
     * @return bool
     */
    public function authenticate($token): bool
    {
        return true;
    }

    /**
     * Populates an element model based on a query result.
     *
     * @param array $row
     *
     * @return Social_LoginAccountModel
     */
    public function populateElementModel($row)
    {
        return Social_LoginAccountModel::populateModel($row);
    }

    /**
     * @inheritdoc
     * @throws Exception if reasons
     */
    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            Craft::$app->db->createCommand()
                ->insert('{{%social_login_accounts}}', [
                    'id' => $this->id,
                    'userId' => $this->userId,
                    'providerHandle' => $this->providerHandle,
                    'socialUid' => $this->socialUid,
                    /*'username' => $this->username,
                    'email' => $this->email,
                    'firstName' => $this->firstName,
                    'lastName' => $this->lastName,
                    'lastLoginDate' => $this->lastLoginDate,*/
                ])
                ->execute();
        } else {
            Craft::$app->db->createCommand()
                ->update('{{%social_login_accounts}}', [
                    'userId' => $this->userId,
                    'providerHandle' => $this->providerHandle,
                    'socialUid' => $this->socialUid,
                    /*'username' => $this->username,
                    'email' => $this->email,
                    'firstName' => $this->firstName,
                    'lastName' => $this->lastName,
                    'lastLoginDate' => $this->lastLoginDate,*/
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }

    /**
     * Finds an identity by the given ID.
     *
     * @param string|int $id the ID to be looked for
     *
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        // TODO: Implement findIdentity() method.
    }

    /**
     * Finds an identity by the given token.
     *
     * @param mixed $token the token to be looked for
     * @param mixed $type  the type of the token. The value of this parameter depends on the implementation.
     *                     For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     *
     * @return IdentityInterface the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // TODO: Implement findIdentityByAccessToken() method.
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     *
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
    }

    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     *
     * @param string $authKey the given auth key
     *
     * @return bool whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
    }

    /**
     * @return array
     */
    protected static function defineTableAttributes(): array
    {
        if (Craft::$app->getConfig()->get('useEmailAsUsername'))
        {
            // Start with Email and don't even give Username as an option
            $attributes = array(
                'email' => array('label' => Craft::t('app', 'Email')),
            );
        }
        else
        {
            $attributes = array(
                'username' => array('label' => Craft::t('app', 'Username')),
                'email'    => array('label' => Craft::t('app', 'Email')),
            );
        }

        $attributes['fullName'] = array('label' => Craft::t('app', 'Full Name'));
        $attributes['firstName'] = array('label' => Craft::t('app', 'First Name'));
        $attributes['lastName'] = array('label' => Craft::t('app', 'Last Name'));

        $attributes['providerHandle'] = array('label' => Craft::t('app', 'Login Provider'));
        $attributes['socialUid']     = array('label' => Craft::t('app', 'Social User ID'));

        $attributes['userId']        = array('label' => Craft::t('app', 'User ID'));
        $attributes['lastLoginDate'] = array('label' => Craft::t('app', 'Last Login'));
        $attributes['dateCreated']   = array('label' => Craft::t('app', 'Date Created'));
        $attributes['dateUpdated']   = array('label' => Craft::t('app', 'Date Updated'));


        // Allow plugins to modify the attributes

        /*
        $pluginAttributes = Craft::$app->getPlugins()->call('defineAdditionalLoginAccountTableAttributes', array(), true);

        foreach ($pluginAttributes as $thisPluginAttributes)
        {
            $attributes = array_merge($attributes, $thisPluginAttributes);
        }
        */

        return $attributes;
    }

    /**
     * Returns the list of table attribute keys that should be shown by default.
     *
     * @param string $source The selected source’s key
     *
     * @return string[] The table attributes.
     * @see defaultTableAttributes()
     * @see tableAttributes()
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return ['username', 'fullName', 'providerHandle', 'socialUid', 'lastLoginDate'];
    }
}
