<?php
/**
 * @link      https://dukt.net/social/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/social/docs/license
 */

namespace dukt\social\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;
use dukt\social\elements\db\LoginAccountQuery;
use dukt\social\Plugin as Social;

/**
 * Class LoginAccount
 *
 * @package dukt\social\elements
 */
class LoginAccount extends Element
{
    // Private Properties
    // =========================================================================

    /**
     * @var
     */
    private $_user;

    // Public Properties
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
        return (string)$this->socialUid;
    }

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

    /**
     * @inheritdoc
     *
     * @return LoginAccountQuery The newly created [[LoginAccountQuery]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new LoginAccountQuery(get_called_class());
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

        $loginProviders = Social::$plugin->getLoginProviders()->getLoginProviders();

        if ($loginProviders) {
            $sources[] = ['heading' => Craft::t('social', 'Login Providers')];

            foreach ($loginProviders as $loginProvider) {
                $providerHandle = $loginProvider->getHandle();
                $key = 'group:'.$providerHandle;

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
    protected static function defineTableAttributes(): array
    {
        $attributes['socialUid'] = ['label' => Craft::t('social', 'Social User ID')];
        $attributes['username'] = ['label' => Craft::t('social', 'Username')];
        $attributes['fullName'] = ['label' => Craft::t('social', 'Full Name')];
        $attributes['email'] = ['label' => Craft::t('social', 'Email')];
        $attributes['provider'] = ['label' => Craft::t('social', 'Login Provider')];
        $attributes['dateCreated'] = ['label' => Craft::t('social', 'Date Created')];
        $attributes['dateUpdated'] = ['label' => Craft::t('social', 'Date Updated')];
        $attributes['lastLoginDate'] = ['label' => Craft::t('social', 'Last Login')];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'username':
                $user = $this->getUser();
                return $user ? Craft::$app->getView()->renderTemplate('_elements/element', ['element' => $user]) : '';
            case 'email':
                $user = $this->getUser();
                return $user ? Html::encodeParams('<a href="mailto:{email}">{email}</a>', ['email' => $user->email]) : '';
            case 'fullName':
                $user = $this->getUser();
                return $user && $user->getFullName() ? $user->getFullName() : '';
            case 'provider':
                $loginProvider = $this->getLoginProvider();
                return $loginProvider ? Craft::$app->getView()->renderTemplate('social/loginaccounts/_element', ['loginProvider' => $loginProvider]) : '';
        }

        return parent::tableAttributeHtml($attribute);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
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
     * @return \dukt\social\base\LoginProvider|null
     */
    public function getLoginProvider()
    {
        if ($this->providerHandle) {
            return Social::$plugin->getLoginProviders()->getLoginProvider($this->providerHandle);
        }
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        if (!isset($this->_user)) {
            if ($this->userId) {
                $this->_user = Craft::$app->users->getUserById($this->userId);
            }
        }

        return $this->_user;
    }

    /**
     * @param $token
     *
     * @return bool
     */
    public function authenticate($token): bool
    {
        // Todo: Check authentication again with token?
        return true;
    }

    /**
     * @inheritdoc
     */
    public function datetimeAttributes(): array
    {
        $names = parent::datetimeAttributes();
        $names[] = 'lastLoginDate';

        return $names;
    }
}
