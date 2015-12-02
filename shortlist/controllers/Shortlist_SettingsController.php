<?php
namespace Craft;

class Shortlist_SettingsController extends BaseController
{
    private $plugin;

    public function init()
    {
        $this->plugin = craft()->plugins->getPlugin('shortlist');
    }

    public function actionIndex()
    {
        $variables = [
            'edition'     => craft()->shortlist_license->getEdition(),
            'editionName' => craft()->shortlist_license->getEditionName(),
            'settings' => $this->plugin->getSettings()];

        $this->renderTemplate('shortlist/settings/_index', $variables);
    }


    public function actionLicense(array $variables = [])
    {

        craft()->shortlist_license->ping(true);

        $variables = [
            'edition'     => craft()->shortlist_license->getEdition(),
            'editionName' => craft()->shortlist_license->getEditionName()];


        $this->renderTemplate('shortlist/settings/_license', $variables);
    }


    private function saveSettings($group, $data = [])
    {
        $this->requireAdmin();
        $this->requirePostRequest();
        $settings = craft()->request->getPost($group);

        $settings = [$group => $settings];
        if (craft()->plugins->savePluginSettings($this->plugin, $settings)) {
            craft()->userSession->setNotice(Craft::t('Settings saved.'));
            $this->redirectToPostedUrl();
        }
        craft()->userSession->setError(Craft::t('Couldn\'t save the settings.'));

        // Send the plugin back to the template
        craft()->urlManager->setRouteVariables([]);
    }
}
