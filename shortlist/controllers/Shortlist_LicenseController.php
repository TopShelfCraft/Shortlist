<?php
namespace Craft;

class Shortlist_LicenseController extends BaseController
{
    public function __construct()
    {
        craft()->shortlist_license->ping();
    }

    public function actionIndex(array $variables = [])
    {
        $variables['currentEdition'] = 'Dev';
        $this->renderTemplate('shortlist/settings/_edition', $variables);
    }


    public function actionGetUpgradeModal()
    {
        $this->requireAjaxRequest();

        // Make it so Craft Client accounts can perform the upgrade.
        if (craft()->getEdition() == Craft::Pro) {
            craft()->userSession->requireAdmin();
        }

        $etResponse = craft()->shortlist_license->fetchUpgradeInfo();

        if (!$etResponse) {
            $this->returnErrorJson(Craft::t('Shortlist is unable to fetch edition info at this time.'));
        }
        /*

        // Make sure we've got a valid license key (mismatched domain is OK for these purposes)
        if ($etResponse->licenseKeyStatus == LicenseKeyStatus::Invalid)
        {
            $this->returnErrorJson(Craft::t('Your license key is invalid.'));
        }

        // Make sure they've got a valid licensed edition, just to be safe
        if (!AppHelper::isValidEdition($etResponse->licensedEdition))
        {
            $this->returnErrorJson(Craft::t('Your license has an invalid Shortlist edition associated with it.'));
        }
*/
        $editions = array();

        foreach ($etResponse->data->editions as $edition => $info) {
            $editions[$edition]['price'] = $info['price'];
            $editions[$edition]['formattedPrice'] = craft()->numberFormatter->formatCurrency($info['price'], 'USD', true);

            if (isset($info['salePrice']) && $info['salePrice'] < $info['price']) {
                $editions[$edition]['salePrice'] = $info['salePrice'];
                $editions[$edition]['formattedSalePrice'] = craft()->numberFormatter->formatCurrency($info['salePrice'], 'USD', true);
            } else {
                $editions[$edition]['salePrice'] = null;
            }
        }


        $canTestEditions = craft()->canTestEditions();

        $modalHtml = craft()->templates->render('shortlist/settings/_upgrademodal', array(
            'editions'        => $editions,
            'licensedEdition' => craft()->shortlist_license->getEdition(),//$etResponse->licensedEdition,
            'canTestEditions' => $canTestEditions
        ));

        $this->returnJson(array(
            'success'         => true,
            'editions'        => $editions,
            'licensedEdition' => craft()->shortlist_license->getEdition(),//$etResponse->licensedEdition,
            'canTestEditions' => $canTestEditions,
            'modalHtml'       => $modalHtml,
            'stripePublicKey' => $etResponse->data->stripePublicKey,
        ));
    }


    /**
     * Passes along a given CC token to Elliott to purchase a Craft edition.
     *
     * @return null
     */
    public function actionPurchaseUpgrade()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();

        // Make it so Craft Client accounts can perform the upgrade.
        if (craft()->getEdition() == Craft::Pro) {
            craft()->userSession->requireAdmin();
        }

        $model = new Shortlist_UpgradePurchaseModel(array(
            'ccTokenId'     => craft()->request->getRequiredPost('ccTokenId'),
            'product'       => 'shortlist',
            'edition'       => craft()->request->getRequiredPost('edition'),
            'expectedPrice' => craft()->request->getRequiredPost('expectedPrice'),
        ));


        if (craft()->shortlist_license->purchaseUpgrade($model))
        {
            $this->returnJson(array(
                'success' => true,
                'edition' => $model->edition
            ));
        }
        else
        {
            $this->returnJson(array(
                'errors' => $model->getErrors()
            ));
        }


    }

    /**
     * Tries a Craft edition on for size.
     *
     * @throws Exception
     * @return null
     */
    public function actionTestUpgrade()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();
        craft()->userSession->requireAdmin();

        if (!craft()->canTestEditions()) {
            throw new Exception('Tried to test an edition, but Shortlist isn\'t allowed to do that.');
        }

        $edition = craft()->request->getRequiredPost('edition');
        craft()->shortlist_license->setEdition($edition);

        $this->returnJson(array(
            'success' => true
        ));
    }


}
