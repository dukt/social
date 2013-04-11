<?php

namespace Craft;

class Connect_ServiceRecord extends BaseRecord
{
    /**
     * Get Table Name
     */ 
    public function getTableName()
    {
        return 'connect_services';
    }
    
    // --------------------------------------------------------------------
    
    /**
     * Define Attributes
     */ 
    public function defineAttributes()
    {
        return array(
            'providerClass' => array(AttributeType::String, 'required' => true, 'unique' => true),
            'clientId' => array(AttributeType::String, 'required' => true),
            'clientSecret' => array(AttributeType::String, 'required' => true),
            'token' => array(AttributeType::String, 'column' => ColumnType::Text),
        );
    }

    public function create()
    {
        $class = get_class($this);

        $record = new $class();

        return $record;
    }
}