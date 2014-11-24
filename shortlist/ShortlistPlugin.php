<?php
namespace Craft;

class ShortlistPlugin extends BasePlugin
{
	function getName()
	{
		return Craft::t('Shortlist');
	}

	function getVersion()
	{
		return '0.1';
	}

	function getDeveloper()
	{
		return 'Square Bit';
	}

	function getDeveloperUrl()
	{
		return 'http://squarebit.co.uk';
	}

	public function hasCpSection()
    {
        return false;
    }

    public function registerCpRoutes()
    {
        return array(
            'charge/detail/(?P<chargeId>\d+)' 	=> array('action' => 'charge/view'),
            'charge/coupons' 					=> array('action' => 'charge/coupon/all'),
            'charge/coupons/new' 				=> array('action' => 'charge/coupon/edit'),
            'charge/coupons/(?P<couponId>\d+)' 	=> array('action' => 'charge/coupon/edit')
        );

    }
/*
	protected function defineSettings()
	{
		return array(
			'stripeAccountMode'			=> array(AttributeType::String, 'required' => true),
			'stripeTestCredentialsSK' 	=> array(AttributeType::String, 'required' => true),
			'stripeTestCredentialsPK' 	=> array(AttributeType::String, 'required' => true),
			'stripeLiveCredentialsSK' 	=> array(AttributeType::String, 'required' => true),
			'stripeLiveCredentialsPK' 	=> array(AttributeType::String, 'required' => true),
			'stripeDefaultCurrency' 	=> array(AttributeType::String, 'required' => true),
		);
	}

	public function getSettingsHtml()
	{
		$currencies = array();

		foreach($this->getCurrencies('all') as $key => $currency) {
			$currencies[strtoupper($key)] = strtoupper($key) . ' - '. $currency['name'];
		}


		return craft()->templates->render('charge/_settings', array(
			'settings' => $this->getSettings(),
			'currencies' => $currencies,
			'accountModes'	=> array('test' => 'Test Mode', 'live' => 'Live Mode')
		));
	}*/
}
