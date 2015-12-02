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
        return '1.1.0.dev1';
    }

    function getSchemaVersion()
    {
        return '1.1.0';
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

    function getDocumentationUrl()
    {
        return 'https://squarebit.co.uk/software/craft/shortlist';
    }

    function getReleaseFeedUrl()
    {
        return 'https://squarebit.co.uk/software/craft/shortlist/updates.json';
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


            'shortlist/settings'             => ['action' => 'shortlist/settings/index'],
            'shortlist/settings/license'     => ['action' => 'shortlist/settings/license'],
            'shortlist/settings/listelement' => ['action' => 'shortlist/list/editFields'],
            'shortlist/settings/itemelement' => ['action' => 'shortlist/item/editFields'],
        ];

    }

    protected function defineSettings()
    {
        return [
            'defaultListTitle' => [AttributeType::String, 'required' => true, 'default' => 'Wishlist'],
            'defaultListName'  => [AttributeType::String, 'required' => true, 'default' => 'wishlist'],
            'defaultListSlug'  => [AttributeType::String, 'required' => true, 'default' => 'wishlist'],
        ];
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
        $context['navItems']['setup'] = ['title' => Craft::t('Setup')];

        $context['navItems']['fields'] = ['heading' => Craft::t('Fields')];
        $context['navItems']['itemelement'] = ['title' => Craft::t('Item Fields')];
        $context['navItems']['listelement'] = ['title' => Craft::t('List Fields')];

        if (craft()->shortlist_license->getEdition() > 0) {
            $context['navItems']['emails'] = ['title' => Craft::t('Emails')];
            $context['navItems']['subscriptions'] = ['title' => Craft::t('Subscriptions')];
        }
        $context['navItems']['developer'] = ['heading' => Craft::t('Developer')];
        $context['navItems']['license'] = ['title' => Craft::t('License')];

    }


    /**
     * Includes front end resources for Control Panel requests.
     */
    private function includeCpResources()
    {
        $templatesService = craft()->templates;
        $templatesService->includeJsResource('charge/cp/js/shortlist.js');
    }


}
