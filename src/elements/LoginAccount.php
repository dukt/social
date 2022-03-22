<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2021, Dukt
 * @license   https://github.com/dukt/social/blob/v2/LICENSE.md
 */

namespace dukt\social\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\Html;
use dukt\social\elements\db\LoginAccountQuery;
use dukt\social\Plugin;
use dukt\social\models\Token;


/**
 * LoginAccount represents a login account element.
 *
 * @property int $userId
 * @property string $providerHandle
 * @property string $socialUid
 *
 * @author Dukt <support@dukt.net>
 * @since  2.0
 */
class LoginAccount extends Element
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     *
     * @return LoginAccountQuery The newly created [[LoginAccountQuery]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new LoginAccountQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('social', 'All login accounts'),
                'criteria' => [],
                'hasThumbs' => false
            ]
        ];

        $loginProviders = Plugin::getInstance()->getLoginProviders()->getLoginProviders();

        if ($loginProviders) {
            $sources[] = ['heading' => Craft::t('social', 'Login Providers')];

            foreach ($loginProviders as $loginProvider) {
                $providerHandle = $loginProvider->getHandle();
                $key = 'group:' . $providerHandle;

                $sources[] = [
                    'key' => $key,
                    'label' => Craft::t('social', $loginProvider->getName()),
                    'criteria' => ['providerHandle' => $providerHandle],
                    'hasThumbs' => false
                ];
            }
        }

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return ['socialUid', 'username', 'firstName', 'lastName', 'fullName', 'email', 'loginProvider'];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        $attributes = [];
        $attributes['socialUid'] = Craft::t('social', 'Social User ID');
        $attributes['username'] = Craft::t('social', 'Username');
        $attributes['email'] = Craft::t('social', 'Email');
        $attributes['providerHandle'] = Craft::t('social', 'Login Provider');
        $attributes['elements.dateCreated'] = Craft::t('social', 'Date Created');
        $attributes['elements.dateUpdated'] = Craft::t('social', 'Date Updated');
        $attributes['lastLoginDate'] = Craft::t('social', 'Last Login');

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [];
        $attributes['socialUid'] = ['label' => Craft::t('social', 'Social User ID')];
        $attributes['username'] = ['label' => Craft::t('social', 'Username')];
        $attributes['fullName'] = ['label' => Craft::t('social', 'Full Name')];
        $attributes['email'] = ['label' => Craft::t('social', 'Email')];
        $attributes['loginProvider'] = ['label' => Craft::t('social', 'Login Provider')];
        $attributes['dateCreated'] = ['label' => Craft::t('social', 'Date Created')];
        $attributes['dateUpdated'] = ['label' => Craft::t('social', 'Date Updated')];
        $attributes['lastLoginDate'] = ['label' => Craft::t('social', 'Last Login')];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return ['username', 'fullName', 'email', 'loginProvider', 'dateCreated', 'lastLoginDate'];
    }

    // Properties
    // =========================================================================

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
    private $_user;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function __toString()
    {
        return (string)$this->socialUid;
    }

    /**
     * Authenticate.
     *
     * @param Token $token
     *
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function authenticate(Token $token): bool
    {
        $socialLoginProvider = Plugin::getInstance()->getLoginProviders()->getLoginProvider($token->providerHandle);

        $profile = $socialLoginProvider->getProfile($token);

        $userFieldMapping = $socialLoginProvider->getUserFieldMapping();
        $socialUid = Craft::$app->getView()->renderString($userFieldMapping['id'], ['profile' => $profile]);

        return $this->socialUid === $socialUid;
    }

    /**
     * Get login provider.
     *
     * @return \dukt\social\base\LoginProvider|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getLoginProvider()
    {
        if ($this->providerHandle) {
            return Plugin::getInstance()->getLoginProviders()->getLoginProvider($this->providerHandle);
        }
    }

    /**
     * Get user.
     *
     * @return User|null
     */
    public function getUser()
    {
        if (!isset($this->_user) && $this->userId) {
            $this->_user = Craft::$app->users->getUserById($this->userId);
        }

        return $this->_user;
    }

    /**
     * Gets the user's username.
     *
     * @return string|null
     */
    public function getUsername()
    {
        $user = $this->getUser();

        if ($user) {
            return $user->username;
        }
    }

    /**
     * Gets the user's first name.
     *
     * @return string|null
     */
    public function getFirstName()
    {
        $user = $this->getUser();

        if ($user) {
            return $user->firstName;
        }
    }

    /**
     * Gets the user's last name.
     *
     * @return string|null
     */
    public function getLastName()
    {
        $user = $this->getUser();

        if ($user) {
            return $user->lastName;
        }
    }

    /**
     * Gets the user's full name.
     *
     * @return string|null
     */
    public function getFullName()
    {
        $user = $this->getUser();

        if ($user) {
            return $user->getFullName();
        }
    }

    /**
     * Gets the user's email.
     *
     * @return string|null
     */
    public function getEmail()
    {
        $user = $this->getUser();

        if ($user) {
            return $user->email;
        }
    }

    // Indexes, etc.
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'username':
                return $this->_usernameTableAttributeHtml();
            case 'email':
                return $this->_emailTableAttributeHtml();
            case 'lastLoginDate':
                return $this->_lastLoginDateTableAttributeHtml();
            case 'loginProvider':
                return $this->_loginProviderTableAttributeHtml();
        }

        return parent::tableAttributeHtml($attribute);
    }

    // Events
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
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
                ])
                ->execute();
        } else {
            Craft::$app->db->createCommand()
                ->update('{{%social_login_accounts}}', [
                    'userId' => $this->userId,
                    'providerHandle' => $this->providerHandle,
                    'socialUid' => $this->socialUid,
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }


    // Private Methods
    // -------------------------------------------------------------------------

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    private function _usernameTableAttributeHtml()
    {
        $user = $this->getUser();

        return $user ? Craft::$app->getView()->renderTemplate('_elements/element', ['element' => $user]) : '';
    }

    /**
     * @return string
     */
    private function _emailTableAttributeHtml()
    {
        $user = $this->getUser();

        return $user ? Html::encodeParams('<a href="mailto:{email}">{email}</a>', ['email' => $user->email]) : '';
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    private function _lastLoginDateTableAttributeHtml()
    {
        $user = $this->getUser();

        return Craft::$app->getFormatter()->asTime($user->lastLoginDate, 'short');
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    private function _loginProviderTableAttributeHtml()
    {
        $loginProvider = $this->getLoginProvider();

        return $loginProvider ? Craft::$app->getView()->renderTemplate('social/loginaccounts/_element', ['loginProvider' => $loginProvider]) : '';
    }
}
