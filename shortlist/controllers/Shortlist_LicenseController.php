<?php
namespace Craft;

class Shortlist_LicenseController extends BaseController
{

    public function actionEdit()
    {
        $licenseKey = craft()->shortlist_license->getLicenseKey();

        $this->renderTemplate('shortlist/settings/license', [
            'hasLicenseKey' => ($licenseKey !== null)
        ]);
    }

    public function actionGetLicenseInfo()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        return $this->sendResponse(craft()->shortlist_license->getLicenseInfo());
    }

    public function actionUpdateLicenseKey()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        $licenseKey = craft()->request->getRequiredPost('licenseKey');

        // Are we registering a new license key?
        if ($licenseKey) {
            // Record the license key locally
            try {
                craft()->shortlist_license->setLicenseKey($licenseKey);
            } catch (InvalidLicenseKeyException $e) {
                $this->returnErrorJson(Craft::t('That license key is invalid.'));
            }

            return $this->sendResponse(craft()->shortlist_license->registerPlugin($licenseKey));
        } else {
            // Just clear our record of the license key
            craft()->shortlist_license->setLicenseKey(null);
            craft()->plugins->setPluginLicenseKeyStatus('Shortlist', LicenseKeyStatus::Unknown);
            return $this->sendResponse();

        }
    }




    public function actionUnregister()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        return $this->sendResponse(craft()->shortlist_license->unregisterLicenseKey());
    }



    public function actionTransfer()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        return $this->sendResponse(craft()->shortlist_license->transferLicenseKey());
    }



    private function sendResponse($success = true)
    {
        if($success) {
            $this->returnJson([
                'success'          => true,
                'licenseKey'       => craft()->shortlist_license->getLicenseKey(),
                'licenseKeyStatus' => craft()->plugins->getPluginLicenseKeyStatus('Shortlist'),
            ]);
        } else {
            $this->returnErrorJson(craft()->shortlist_license->error);
        }
    }
}
