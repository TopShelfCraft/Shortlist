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
		return '0.3';
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
        return true;
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

	protected function defineSettings()
	{
		return array(
			'defaultListTitle'			=> array(AttributeType::String, 'required' => true),
			'defaultListName'			=> array(AttributeType::String, 'required' => true),
			'defaultListSlug'			=> array(AttributeType::String, 'required' => true),
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('shortlist/_settings', array(
			'settings' => $this->getSettings(),
		));
	}
}
