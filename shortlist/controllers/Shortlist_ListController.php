<?php
namespace Craft;

class Shortlist_ListController extends BaseController
{
    protected $allowAnonymous = true;
    private $baseFields = array('listTitle', 'listSlug', 'listName');

    public function init()
    {
        // This will grab our current user be they member or guest
        craft()->shortlist->getUser();
    }


    /**
     * Add, Remove, Toggle
     *
     * All are just stub functions passing to handleAction where all the core logic is shared
     */
    public function actionAdd()
    {
        $this->handleAction('new');
    }

    public function actionNew()
    {
        $this->handleAction('new');
    }

    public function actionRemove()
    {
        $this->handleAction('remove');
    }

    public function actionDelete()
    {
        $this->handleAction('remove');
    }

    public function actionMakeDefault()
    {
        $this->handleAction('makeDefault');
    }


    /**
     * Handle Action
     *
     * Does all the leg work for the common actions
     */
    public function handleAction($actionType)
    {
        $listId = craft()->shortlist->getIdForRequest('listId,id');

        // collect any extra info we might have.
        // This could be a combo of get and post data
        $extraData = craft()->shortlist->getExtraForRequest($this->baseFields);

        // Pass to the service to do the leg work
        $response = craft()->shortlist_list->action($actionType, $listId, $extraData);
        if ($response == false) {
            // Deal with an error state
            die('failed to act'); // @todo
        }

        // Return as appropriate
        if (craft()->request->isAjaxRequest()) {
            $this->returnJson($response);
        } else {
            craft()->shortlist->redirect($response['object']);
        }

    }


    public function actionView(array $variables = array())
    {
        $this->requireAdmin();

        $listId = $variables['listId'];
        $list = craft()->shortlist_list->getListById($listId);

        if ($list == null) $this->redirect('shortlist');

        $variables['list'] = $list;

        $variables['tabs']['List'] = array(
            'label' => Craft::t('List Details'),
            'url'   => '#list',
        );
        $variables['tabs']['Items'] = array(
            'label' => Craft::t('Items'),
            'url'   => '#items',
        );
        $variables['tabs']['Related'] = array(
            'label' => Craft::t('Related'),
            'url'   => '#related',
        );

        // Grab the item elements
        $criteria = craft()->elements->getCriteria('shortlist_item');
        $criteria->listId = $listId;
        $variables['listItems'] = $criteria->find();

        $this->renderTemplate('shortlist/list/_view', $variables);
    }


}