<?php
namespace Craft;

class Social_LoginAccountElementType extends BaseElementType
{
    /**
     * Returns the element type name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Login Accounts');
    }

    // /**
    //  * Returns whether this element type can have statuses.
    //  *
    //  * @return bool
    //  */
    // public function hasStatuses()
    // {
    //     return true;
    // }

    // /**
    //  * Returns all of the possible statuses that elements of this type may have.
    //  *
    //  * @return array|null
    //  */
    // public function getStatuses()
    // {
    //     return array(
    //         UserStatus::Active    => Craft::t('Active'),
    //         UserStatus::Pending   => Craft::t('Pending'),
    //         UserStatus::Locked    => Craft::t('Locked'),
    //         UserStatus::Suspended => Craft::t('Suspended'),
    //     );
    // }

    /**
     * Returns this element type's sources.
     *
     * @param string|null $context
     *
     * @return array|false
     */
    public function getSources($context = null)
    {
        $sources = array(
            '*' => array(
                'label' => Craft::t('All login providers'),
            )
        );
        return $sources;
    }

    // /**
    //  * Defines which element model attributes should be searchable.
    //  *
    //  * @return array
    //  */
    // public function defineSearchableAttributes()
    // {
    //     return array(
    //         'userId',
    //         'tokenId'
    //     );
    // }

    /**
     * Defines the attributes that elements can be sorted by.
     *
     * @return array
     */
    public function defineSortableAttributes()
    {
        return array(
            'userId' => Craft::t('User ID'),
            'tokenId' => Craft::t('Token ID')
        );
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
                'email' => array('label' => Craft::t('Email')),
            );
        }
        else
        {
            $attributes = array(
                'username' => array('label' => Craft::t('Username')),
                'email'    => array('label' => Craft::t('Email')),
            );
        }

        $attributes['fullName'] = array('label' => Craft::t('Full Name'));
        $attributes['firstName'] = array('label' => Craft::t('First Name'));
        $attributes['lastName'] = array('label' => Craft::t('Last Name'));

        $attributes['oauthProvider'] = array('label' => Craft::t('OAuth Provider'));
        $attributes['socialUid']     = array('label' => Craft::t('Social User ID'));

        $attributes['userId']        = array('label' => Craft::t('User ID'));
        $attributes['lastLoginDate'] = array('label' => Craft::t('Last Login'));
        $attributes['dateCreated']   = array('label' => Craft::t('Date Created'));
        $attributes['dateUpdated']   = array('label' => Craft::t('Date Updated'));

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
        return array('username', 'fullName', 'oauthProvider', 'socialUid', 'lastLoginDate');
    }

    /**
     * Returns the HTML that should be shown for a given element’s attribute in Table View.
     *
     * @param BaseElementModel $element
     * @param string           $attribute
     *
     * @return string
     */
    public function getTableAttributeHtml(BaseElementModel $element, $attribute)
    {
        switch ($attribute)
        {
            case 'oauthProvider':
            {
                // TODO:consider eager loading the provider
                $provider = craft()->oauth->getProvider($element->providerHandle);

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
                return parent::getTableAttributeHtml($element, $attribute);
            }
        }
    }

    /**
     * Defines any custom element criteria attributes for this element type.
     *
     * @return array
     */
    public function defineCriteriaAttributes()
    {
        return array(
            'userId' => AttributeType::Number,
            'tokenId' => AttributeType::Number,
            'providerHandle' => AttributeType::String,
            'socialUid' => AttributeType::String,

            'username' => AttributeType::String,
            'email' => AttributeType::String,
            'firstName' => AttributeType::String,
            'lastName' => AttributeType::String,
            'lastLoginDate' => AttributeType::DateTime,
        );
    }

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
            login_accounts.tokenId,
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

        if ($criteria->tokenId)
        {
            $query->andWhere(DbHelper::parseParam('login_accounts.tokenId', $criteria->tokenId, $query->params));
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
}
