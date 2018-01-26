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
        return '1.1.1';
    }

    function getSchemaVersion()
    {
        return '1.1.0.0';
    }

    function getDeveloper()
    {
        return 'Top Shelf Craft';
    }

    function getDeveloperUrl()
    {
        return 'http://topshelfcraft.com';
    }

    public function hasCpSection()
    {
        return true;
    }


    public function registerCpRoutes()
    {
        return array(
            'shortlist/list'                 => array('action' => 'shortlist/list/index'),
            'shortlist/list/(?P<listId>\d+)' => array('action' => 'shortlist/list/view'),
            'shortlist/list/editFields'      => array('action' => 'shortlist/list/editFields'),


            'shortlist/item'                 => array('action' => 'shortlist/item/index'),
            'shortlist/item/(?P<itemId>\d+)' => array('action' => 'shortlist/item/view')

            /*
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
