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
        return '0.4';
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
            'shortlist/list/(?P<listId>\d+)'   => array('action' => 'shortlist/list/view'),

            'shortlist/list/editFields'        => array('action' => 'shortlist/list/editFields'),
            /*
            'shortlist/items'                  => array('action' => 'shortlist/items'),
            'shortlist/items/(?P<itemId>\d+)'  => array('action' => 'shortlist/item/view'),
            'shortlist/users'                  => array('action' => 'shortlist/users'),
            'shortlist/users/(?P<itemId>\d+)'  => array('action' => 'shortlist/users/view'),*/
        );

    }

    protected function defineSettings()
    {
        return array(
            'defaultListTitle' => array(AttributeType::String, 'required' => true, 'default' => 'Wishlist'),
            'defaultListName'  => array(AttributeType::String, 'required' => true, 'default' => 'wishlist'),
            'defaultListSlug'  => array(AttributeType::String, 'required' => true, 'default' => 'wishlist'),
        );
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('shortlist/_settings', array(
            'settings' => $this->getSettings(),
        ));
    }
}
