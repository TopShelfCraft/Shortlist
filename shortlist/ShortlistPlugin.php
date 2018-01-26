<?php
namespace Craft;


class ShortlistPlugin extends BasePlugin
{
    public function init()
    {
        if (craft()->request->isCpRequest()) {
            $this->includeCpResources();
            craft()->templates->hook('shortlist.prepCpTemplate', [$this, 'prepCpTemplate']);
            craft()->templates->hook('shortlist.prepCpSettingsTemplate', [$this, 'prepCpSettingsTemplate']);
        }
    }

    function getName()
    {
        return Craft::t('Shortlist');
    }

    function getVersion()
    {
        return '2.0.0.b2';
    }

    function getSchemaVersion()
    {
        return '2.0.0.b2';
    }

    function getDeveloper()
    {
        return 'Top Shelf Craft';
    }

    function getDeveloperUrl()
    {
        return 'https://topshelfcraft.com';
    }

    public function getDescription()
    {
        return 'Lightweight, flexible lists for Craft CMS.';
    }

    public function hasCpSection()
    {
        return true;
    }

    function getDocumentationUrl()
    {
        return 'https://transition.topshelfcraft.com/software/craft/shortlist';
    }

    function getReleaseFeedUrl()
    {
        //return 'https://transition.topshelfcraft.com/software/craft/shortlist/updates.json';
    }

    function getSettingsUrl()
    {
        return 'shortlist/settings';
    }


    public function registerCpRoutes()
    {
        return [
            'shortlist/list'                 => ['action' => 'shortlist/list/index'],
            'shortlist/list/(?P<listId>\d+)' => ['action' => 'shortlist/list/view'],


            'shortlist/item'                 => ['action' => 'shortlist/item/index'],
            'shortlist/item/(?P<itemId>\d+)' => ['action' => 'shortlist/item/view'],

            /*
                'shortlist/users'                  => ['action' => 'shortlist/users'],
                'shortlist/users/(?P<itemId>\d+)'  => ['action' => 'shortlist/users/view'],*/

            'shortlist/settings'                         => ['action' => 'shortlist/settings/index'],
            'shortlist/settings/setup'                         => ['action' => 'shortlist/settings/index'],
            'shortlist/settings/license'                 => ['action' => 'shortlist/license/edit'],
            'shortlist/settings/listelement'             => ['action' => 'shortlist/list/editFields'],
            'shortlist/settings/itemelement'             => ['action' => 'shortlist/item/editFields'],
            'shortlist/settings/emails'                  => ['action' => 'shortlist/email/all'],
            'shortlist/settings/emails/new'              => ['action' => 'shortlist/email/edit'],
            'shortlist/settings/emails/(?P<emailId>\d+)' => ['action' => 'shortlist/email/edit'],
        ];

    }

    protected function defineSettings()
    {
        return [
            'defaultListTitle' => [AttributeType::String, 'required' => true, 'default' => 'Wishlist'],
            'defaultListName'  => [AttributeType::String, 'required' => true, 'default' => 'wishlist'],
            'defaultListSlug'  => [AttributeType::String, 'required' => true, 'default' => 'wishlist'],
            'allowDuplicates'  => [AttributeType::Bool, 'required' => true, 'default' => false],

            'edition'     => [AttributeType::Mixed]];
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('shortlist/_settings', [
            'settings' => $this->getSettings()]);
    }


    public function prepCpTemplate(&$context)
    {
        $context['subnav']['shortlist'] = ['label' => Craft::t('Lists'), 'url' => 'shortlist'];

        if (craft()->userSession->isAdmin()) {
            $context['subnav']['settings'] = ['label' => Craft::t('Settings'), 'url' => 'shortlist/settings'];
        }
    }


    public function prepCpSettingsTemplate(&$context)
    {
        $context['selectedItem'] = craft()->request->getSegment(3, 'setup');

        $context['navItems']['license'] = ['title' => Craft::t('License')];

        $context['navItems']['general'] = ['heading' => Craft::t('General')];
        $context['navItems']['setup'] = ['title' => Craft::t('Settings')];

        $context['navItems']['features'] = ['heading' => Craft::t('Features')];
        $context['navItems']['itemelement'] = ['title' => Craft::t('Item Fields')];
        $context['navItems']['listelement'] = ['title' => Craft::t('List Fields')];
        $context['navItems']['emails'] = ['title' => Craft::t('Emails')];

        //$context['navItems']['developer'] = ['heading' => Craft::t('Developer')];
        //$context['navItems']['logs'] = ['title' => Craft::t('Logs')];
        //$context['navItems']['data'] = ['title' => Craft::t('Data')];

    }


    /**
     * Includes front end resources for Control Panel requests.
     */
    private function includeCpResources()
    {
        $templatesService = craft()->templates;
        $templatesService->includeJsResource('shortlist/cp/js/shortlist.js');
    }


}
