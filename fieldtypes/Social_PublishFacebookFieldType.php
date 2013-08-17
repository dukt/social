<?php


namespace Craft;

class Social_PublishFacebookFieldType extends BaseFieldType
{

	/**
	 * Block type name
	 */
	public function getName()
	{
		return Craft::t('Publish To Facebook Stream');
	}

	// --------------------------------------------------------------------

	/**
	 * Save it
	 */
	public function defineContentAttribute()
	{
		return AttributeType::Bool;
	}

	// --------------------------------------------------------------------

	/**
	 * Show field
	 */
	public function getInputHtml($name, $value)
	{
		return craft()->templates->render('social/_field/facebook', array(
			'name'  => $name,
			'value' => $value
		));
	}

	// --------------------------------------------------------------------

	/**
	 * Prep value
	 */
	public function prepValue($isShared)
	{
		return $isShared;
	}

	// --------------------------------------------------------------------

	public function onAfterElementSave()
	{
        $element = $this->element;
        $fields = $element->section->getFieldLayout()->getFields();

        foreach($fields as $field) {
            if($field->getField()->type == 'Social_PublishFacebook') {
                $handle = $field->getField()->handle;
                if($element->{$handle} == 1) {
                    craft()->social->publishFacebook($element);
                }
            }
        }
	}
	
	// --------------------------------------------------------------------
}
