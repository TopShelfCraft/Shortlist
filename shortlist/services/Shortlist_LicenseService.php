<?php
namespace Craft;

class Shortlist_LicenseService extends BaseApplicationComponent
{
    // Constants
    // =========================================================================

    const Ping = 'http://squarebit.craft.dev/actions/licensor/edition/ping';
    const GetUpgradeInfo = 'http://squarebit.craft.dev/actions/licensor/edition/getUpgradeInfo';
    const PurchaseUpgrade = 'http://squarebit.craft.dev/actions/licensor/edition/purchaseUpgrade';


    const TransferLicense = 'http://squarebit.craft.dev/actions/licensor/edition/transferLicenseToCurrentDomain';
    const GetEditionInfo = 'http://squarebit.craft.dev/actions/licensor/edition/getEditionInfo';


    private $plugin;

    public function init()
    {
        $this->plugin = craft()->plugins->getPlugin('shortlist');
        require craft()->path->getPluginsPath() . 'shortlist/etc/Edition.php';
    }
    // Public Methods
    // =========================================================================

    /**
     * @return EtModel|null
     */
    public function ping()
    {
        $et = new Edition(static::Ping);
        $etResponse = $et->phoneHome();

        return $etResponse;
    }


    /**
     * Creates a new EtModel with provided JSON, and returns it if it's valid.
     *
     * @param array $attributes
     *
     * @return EtModel|null
     */
    public function decodeEtModel($attributes)
    {
        if ($attributes) {
            $attributes = JsonHelper::decode($attributes);
            if (is_array($attributes)) {
                $etModel = new Shortlist_LicenseModel($attributes);

                // Make sure it's valid. (At a minimum, localBuild and localVersion
                // should be set.)
                if ($etModel->validate()) {
                    return $etModel;
                }
            }
        }
    }

    /**
     * Fetches info about the available Craft editions from Elliott.
     *
     * @return EtModel|null
     */
    public function fetchUpgradeInfo()
    {
        $et = new Edition(static::GetUpgradeInfo);
        $etResponse = $et->phoneHome();

        if ($etResponse != null) {
            $etResponse->data = new Shortlist_UpgradeInfoModel($etResponse->data);
        }

        return $etResponse;
    }


    public function setEdition($edition)
    {
        $settings = ['edition' => $edition];
        craft()->plugins->savePluginSettings($this->plugin, $settings);
    }

    public function getEdition()
    {
        $settings = $this->plugin->getSettings();

        if (!isset($settings->edition)) return 0;

        return $settings->edition;
    }


    public function getEditionName()
    {
        $settings = $this->plugin->getSettings();

        if (!isset($settings->edition)) return 'Free';

        switch ($settings->edition) {
            case '0':
                return 'Free';
            case '1':
                return 'Pro';
            default:
                return 'Dev';
        }
    }

    /**
     * Attempts to purchase an edition upgrade.
     *
     * @param Shortlist_UpgradePurchaseModel $model
     *
     * @return bool
     */
    public function purchaseUpgrade(Shortlist_UpgradePurchaseModel $model)
    {
        if ($model->validate()) {
            $et = new Edition(static::PurchaseUpgrade);
            $et->setData($model);
            $etResponse = $et->phoneHome();

            if (!empty($etResponse->data['success'])) {
                // Success! Let's get this sucker installed.
                $this->setEdition($model->edition);

                return true;
            } else {
                // Did they at least say why?
                if (!empty($etResponse->errors)) {
                    switch ($etResponse->errors[0]) {
                        // Validation errors
                        case 'edition_doesnt_exist':
                            $error = Craft::t('The selected edition doesn’t exist anymore.');
                            break;
                        case 'invalid_license_key':
                            $error = Craft::t('Your license key is invalid.');
                            break;
                        case 'license_has_edition':
                            $error = Craft::t('Your Shortlist license already has this edition.');
                            break;
                        case 'price_mismatch':
                            $error = Craft::t('The cost of this edition just changed.');
                            break;
                        case 'unknown_error':
                            $error = Craft::t('An unknown error occurred.');
                            break;

                        // Stripe errors
                        case 'incorrect_number':
                            $error = Craft::t('The card number is incorrect.');
                            break;
                        case 'invalid_number':
                            $error = Craft::t('The card number is invalid.');
                            break;
                        case 'invalid_expiry_month':
                            $error = Craft::t('The expiration month is invalid.');
                            break;
                        case 'invalid_expiry_year':
                            $error = Craft::t('The expiration year is invalid.');
                            break;
                        case 'invalid_cvc':
                            $error = Craft::t('The security code is invalid.');
                            break;
                        case 'incorrect_cvc':
                            $error = Craft::t('The security code is incorrect.');
                            break;
                        case 'expired_card':
                            $error = Craft::t('Your card has expired.');
                            break;
                        case 'card_declined':
                            $error = Craft::t('Your card was declined.');
                            break;
                        case 'processing_error':
                            $error = Craft::t('An error occurred while processing your card.');
                            break;

                        default:
                            $error = $etResponse->errors[0];
                    }
                } else {
                    // Something terrible must have happened!
                    $error = Craft::t('Shortlist is unable to purchase an edition upgrade at this time.');
                }

                $model->addError('response', $error);
            }
        }

        return false;
    }

}
