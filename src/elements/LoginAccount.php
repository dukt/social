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

class LoginAccount extends Element
{
    public $userId;
    public $providerHandle;
    public $socialUid;
    public $username;
    public $email;
    public $firstName;
    public $lastName;
    public $lastLoginDate;

    public static function find(): ElementQueryInterface
    {
        return new LoginAccountQuery(get_called_class());
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
		craft()->templates->includeJsResource('social/js/social.js');

		return parent::getIndexHtml($criteria, $disabledElementIds, $viewState, $sourceKey, $context, $includeContainer, $showCheckboxes);
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
				'hasThumbs' => false
			)
		);

		$loginProviders = \dukt\social\Plugin::getInstance()->social_loginProviders->getLoginProviders();

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
		/*craft()->plugins->call('modifyLoginAccountSources', array(&$sources, $context));*/

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

		$deleteAction = craft()->elements->getAction('Delete');
		$deleteAction->setParams(array(
			'confirmationMessage' => Craft::t('app', 'Are you sure you want to delete the selected login accounts?'),
			'successMessage'      => Craft::t('app', 'Login accounts deleted.'),
		));
		$actions[] = $deleteAction;

		// Allow plugins to add additional actions
		$allPluginActions = craft()->plugins->call('addLoginAccountActions', array($source), true);

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
	public function defineSortableAttributes()
	{
		if (craft()->config->get('useEmailAsUsername'))
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
		craft()->plugins->call('modifyLoginAccountSortableAttributes', array(&$attributes));

		return $attributes;
	}

	/**
	 * Defines all of the available columns that can be shown in table views.
	 *
	 * @return array
	 */
	public function defineAvailableTableAttributes()
	{
		if (craft()->config->get('useEmailAsUsername'))
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
		$pluginAttributes = craft()->plugins->call('defineAdditionalLoginAccountTableAttributes', array(), true);

		foreach ($pluginAttributes as $thisPluginAttributes)
		{
			$attributes = array_merge($attributes, $thisPluginAttributes);
		}

		return $attributes;
	}

	/**
	 * Returns the list of table attribute keys that should be shown by default.
	 *
	 * @param string|null $source
	 *
	 * @return array
	 */
	public function getDefaultTableAttributes($source = null)
	{
		return array('username', 'fullName', 'providerHandle', 'socialUid', 'lastLoginDate');
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
/*		$pluginAttributeHtml = craft()->plugins->callFirst('getLoginAccountTableAttributeHtml', array($element, $attribute), true);

		if ($pluginAttributeHtml !== null)
		{
			return $pluginAttributeHtml;
		}*/

		switch ($attribute)
		{
			case 'providerHandle':
			{
				// TODO:consider eager loading the provider
				$provider = \dukt\oauth\Plugin::getInstance()->oauth->getProvider($this->providerHandle);

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
	 * Defines any custom element criteria attributes for this element type.
	 *
	 * @return array
	 */
/*	public function defineCriteriaAttributes()
	{
		return array(
			'userId' => AttributeType::Number,
			'providerHandle' => AttributeType::String,
			'socialUid' => AttributeType::String,

			'username' => AttributeType::String,
			'email' => AttributeType::String,
			'firstName' => AttributeType::String,
			'lastName' => AttributeType::String,
			'lastLoginDate' => AttributeType::DateTime,
		);
	}*/

	/**
	 * Modifies an element query targeting elements of this type.
	 *
	 * @param DbCommand            $query
	 * @param ElementCriteriaModel $criteria
	 *
	 * @return null|false
	 */
	public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
	{
		$query->addSelect('
			login_accounts.id,
			login_accounts.userId,
			login_accounts.providerHandle,
			login_accounts.socialUid,

			users.username,
			users.firstName,
			users.lastName,
			users.email,
			users.lastLoginDate,
		');

		$query->join('social_login_accounts login_accounts', 'login_accounts.id = elements.id');
		$query->leftJoin('users users', 'login_accounts.userId = users.id');

		if ($criteria->userId)
		{
			$query->andWhere(DbHelper::parseParam('login_accounts.userId', $criteria->userId, $query->params));
		}

		if ($criteria->providerHandle)
		{
			$query->andWhere(DbHelper::parseParam('login_accounts.providerHandle', $criteria->providerHandle, $query->params));
		}

		if ($criteria->socialUid)
		{
			$query->andWhere(DbHelper::parseParam('login_accounts.socialUid', $criteria->socialUid, $query->params));
		}

		if ($criteria->username)
		{
			$query->andWhere(DbHelper::parseParam('users.username', $criteria->username, $query->params));
		}

		if ($criteria->firstName)
		{
			$query->andWhere(DbHelper::parseParam('users.firstName', $criteria->firstName, $query->params));
		}

		if ($criteria->lastName)
		{
			$query->andWhere(DbHelper::parseParam('users.lastName', $criteria->lastName, $query->params));
		}

		if ($criteria->email)
		{
			$query->andWhere(DbHelper::parseParam('users.email', $criteria->email, $query->params));
		}

		if ($criteria->lastLoginDate)
		{
			$query->andWhere(DbHelper::parseDateParam('users.lastLoginDate', $criteria->lastLoginDate, $query->params));
		}
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
        // Get the login account record
        if (!$isNew) {
            $record = LoginAccountRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid login account ID: '.$this->id);
            }
        } else {
            $record = new LoginAccountRecord();
            $record->id = $this->id;
        }

        $record->userId = $this->userId;
        $record->providerHandle = $this->providerHandle;
        $record->socialUid = $this->socialUid;
        $record->username = $this->username;
        $record->email = $this->email;
        $record->firstName = $this->firstName;
        $record->lastName = $this->lastName;
        $record->lastLoginDate = $this->lastLoginDate;
        $record->save(false);

        parent::afterSave($isNew);
    }
}
