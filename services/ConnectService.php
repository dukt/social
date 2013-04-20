<?php

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'campaigns/vendor/autoload.php');

use ReflectionClass;
use Symfony\Component\Finder\Finder;

class ConnectService extends BaseApplicationComponent
{
    protected $serviceRecord;

    public function __construct($serviceRecord = null)
    {
        $this->serviceRecord = $serviceRecord;
        if (is_null($this->serviceRecord)) {
            $this->serviceRecord = Connect_ServiceRecord::model();
        }
    }

    public function outputToken($providerClass)
    {
        //$provider = $this->getServiceByProviderClass($providerClass);

        $token = craft()->httpSession->get('connectToken.'.$providerClass);
        $token = base64_decode($token);
        $token = unserialize($token);
        return $token;

        $service = $this->service($provider->id);

        return $service->getUserInfo();
    }

    public function getServiceByProviderClass($providerClass)
    {

        // get the option

        $record = Connect_ServiceRecord::model()->find('providerClass=:providerClass', array(':providerClass' => $providerClass));

        if ($record) {

            return Connect_ServiceModel::populateModel($record);
        }

        return new Connect_ServiceModel();
    }

    public function saveService(Connect_ServiceModel &$model)
    {
        $class = $model->getAttribute('providerClass');

        if (null === ($record = Connect_ServiceRecord::model()->find('providerClass=:providerClass', array(':providerClass' => $class)))) {
            $record = $this->serviceRecord->create();
        }

        $record->setAttributes($model->getAttributes());

        if ($record->save()) {
            // update id on model (for new records)

            $model->setAttribute('id', $record->getAttribute('id'));

            // Connect Service

           // $this->connectService($record);

            return true;
        } else {

            $model->addErrors($record->getErrors());

            return false;
        }
    }


    public function newService($attributes = array())
    {
        $model = new Connect_ServiceModel();

        $model->setAttributes($attributes);

        return $model;
    }


    public function connectService($record = false)
    {
        if(!$record)
        {
            $serviceId = craft()->request->getParam('id');

            $record = $this->serviceRecord->findByPk($serviceId);
        }


        $className = $record->className;

        $provider = \OAuth\OAuth::provider($className, array(
            'id' => $record->clientId,
            'secret' => $record->clientSecret,
            'redirect_url' => \Craft\UrlHelper::getActionUrl('campaigns/settings/serviceCallback/', array('id' => $record->id))
        ));

        $provider = $provider->process(function($url, $token = null) {

            if ($token) {
                $_SESSION['token'] = base64_encode(serialize($token));
            }

            header("Location: {$url}");

            exit;

        }, function() {
            return unserialize(base64_decode($_SESSION['token']));
        });


        $token = $provider->token();

        $record->token = base64_encode(serialize($token));

        $record->save();


        craft()->request->redirect(UrlHelper::getUrl('campaigns/settings'));

    }

    public function service($id)
    {

        $service = $this->serviceRecord->findByPk($id);


        $providerParams = array();
        $providerParams['id'] = $service->clientId;
        $providerParams['secret'] = $service->clientSecret;
        $providerParams['redirect_url'] = "http://google.fr";

        try {
            $provider = \OAuth\OAuth::provider($service->providerClass, $providerParams);

            $token = unserialize(base64_decode($service->token));

            // refresh token if needed ?

            if(!$token)
            {
                throw new \Exception('Invalid Token');
            }

            $provider->setToken($token);

        } catch(\Exception $e)
        {
            throw new Exception('Provider couln\'t instantiate : '.$e->getMessage());
        }

        // $serviceClassName = 'Dukt\\Connect\\'.$service->providerClass.'\\Service';

        // $serviceObject = new $serviceClassName($provider);

        $serviceObject = $provider;

        return $serviceObject;
    }

    public function serviceSend($serviceId, $method, $params = array())
    {
        $service = $this->serviceRecord->findByPk($serviceId);


        $providerParams = array();
        $providerParams['id'] = $service->clientId;
        $providerParams['secret'] = $service->clientSecret;
        $providerParams['redirect_url'] = "http://google.fr";

        try {
            $provider = \OAuth\OAuth::provider($service->className, $providerParams);

            $token = unserialize(base64_decode($service->token));

            // refresh token if needed ?

            if(!$token)
            {
                throw new \Exception('Invalid Token');
            }

            $provider->setToken($token);

        } catch(\Exception $e)
        {
            throw new Exception('Provider couln\'t instantiate');
        }

        $serviceClassName = 'Dukt\\Campaigns\\Services\\'.$service->className;

        $request = new $serviceClassName($provider);

        return $request->send($method, $params);

        //return $request;
    }

    public function getProviders()
    {
        $directory = CRAFT_PLUGINS_PATH.'connect/libraries/Dukt/Connect/';

        $result = array();

        $finder = new Finder();

        $files = $finder->directories()->depth(0)->in($directory);

        foreach($files as $file)
        {
            $class = $file->getRelativePathName();

            //$class = substr($class, 0, -4);

            switch($class)
            {
                case "Common":

                break;

                default:
                $result[$class] = $class;
            }
        }

        return $result;

    }

    public function getAllServices()
    {
        $records = $this->serviceRecord->findAll(array('order'=>'t.title'));

        return Campaigns_ServiceModel::populateModels($records, 'id');
    }

    public function getServiceById($id)
    {
        if ($record = $this->serviceRecord->findByPk($id)) {

            return Campaigns_ServiceModel::populateModel($record);
        }
    }


    public function deleteServiceById($id)
    {
        return $this->serviceRecord->deleteByPk($id);
    }

    public function resetServiceToken($providerClass)
    {
        $providerClass = craft()->request->getParam('providerClass');

        $record = Connect_ServiceRecord::model()->find('providerClass=:providerClass', array(':providerClass' => $providerClass));

        if($record)
        {
            $record->token = NULL;
            return $record->save();
        }

        return false;
    }

}

