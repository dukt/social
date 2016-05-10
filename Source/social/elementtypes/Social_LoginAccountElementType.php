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
                'label' => Craft::t('All login accounts'),
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
        return array(
            'username'          => Craft::t('Username'),
            'fullName'          => Craft::t('Full Name'),
            'oauthProviderName' => Craft::t('OAuth Provider'),
            'socialUid'         => Craft::t('Social User ID'),
            'lastLoginDate'     => Craft::t('Last login'),

            'userId'            => Craft::t('User ID'),
            'tokenId'           => Craft::t('Token ID'),
        );
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
        return array('username', 'fullName', 'oauthProviderName', 'socialUid', 'lastLoginDate');
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
            case 'username':
            case 'fullName':
            case 'lastLoginDate':
            {
                return $element->getUser()->{$attribute};
            }

            case 'oauthProviderName':
            {
                $provider = craft()->oauth->getProvider($element->providerHandle);

                return $provider->getName();
            }

            default:
            {
                return parent::getTableAttributeHtml($element, $attribute);
            }
        }
    }

    // /**
    //  * Defines any custom element criteria attributes for this element type.
    //  *
    //  * @return array
    //  */
    // public function defineCriteriaAttributes()
    // {
    //     return array(
    //         'userId' => AttributeType::Number,
    //         'tokenId' => AttributeType::Number,
    //     );
    // }

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
            login_accounts.socialUid'
        );

        $query->join('social_login_accounts login_accounts', 'login_accounts.id = elements.id');
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
