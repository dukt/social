<?php

namespace Craft;

require(CRAFT_PLUGINS_PATH.'connect/vendor/autoload.php');

class Connect_SettingsController extends BaseController
{
    public function actionSaveService()
    {
        $class = craft()->request->getSegment(3);
        
        $model = new Connect_ServiceModel();



        $attributes = craft()->request->getPost('service');
        
        $attributes['providerClass'] = $class;

        $model->setAttributes($attributes);


        if (craft()->connect->saveService($model)) {

            craft()->userSession->setNotice(Craft::t('Service saved.'));

            $this->redirect('connect');
        } else {

            craft()->userSession->setError(Craft::t("Couldn't save service."));

            craft()->urlManager->setRouteVariables(array('service' => $model));
        }
    }

    public function actionDeleteService()
    {
        $id = craft()->request->getRequiredParam('id');

        craft()->connect->deleteServiceById($id);

        craft()->userSession->setNotice(Craft::t('Service deleted.'));

        $this->redirect('connect/settings');
    }

    public function actionResetServiceToken()
    {
        $providerClass = craft()->request->getRequiredParam('providerClass');

        craft()->connect->resetServiceToken($providerClass);

        craft()->userSession->setNotice(Craft::t('Token Reset.'));

        $redirect = UrlHelper::getUrl('connect/settings/'.$providerClass);

        $this->redirect($redirect);

    }

    public function actionServiceCallback()
    {
        craft()->connect->connectService();
    }
}