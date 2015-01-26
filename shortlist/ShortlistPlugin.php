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
        return false;
    }


    protected function defineSettings()
    {
        return array(
            'defaultListTitle' => array(AttributeType::String, 'required' => true),
            'defaultListName'  => array(AttributeType::String, 'required' => true),
            'defaultListSlug'  => array(AttributeType::String, 'required' => true),
        );
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('shortlist/_settings', array(
            'settings' => $this->getSettings(),
        ));
    }
}
