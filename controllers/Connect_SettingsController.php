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

        craft()->campaigns->deleteServiceById($id);

        craft()->userSession->setNotice(Craft::t('Service deleted.'));

        $this->redirect('campaigns/settings');
    }

    public function actionResetServiceToken()
    {
        $id = craft()->request->getRequiredParam('id');

        craft()->campaigns->resetServiceToken($id);

        craft()->userSession->setNotice(Craft::t('Token Reset.'));

        $redirect = UrlHelper::getUrl('campaigns/settings/services/'.$id);

        $this->redirect($redirect);

    }

    public function actionServiceCallback()
    {
        craft()->campaigns->connectService();
    }
}