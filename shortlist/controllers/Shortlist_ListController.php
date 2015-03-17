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
    public function actionUpdate()
    {
        return $this->handleAction('update');
    }

    public function actionAdd()
    {
        return $this->handleAction('new');
    }

    public function actionNew()
    {
        return $this->handleAction('new');
    }

    public function actionRemove()
    {
        return $this->handleAction('remove');
    }

    public function actionDelete()
    {
        return $this->handleAction('remove');
    }

    public function actionMakeDefault()
    {
        return $this->handleAction('makeDefault');
    }

    public function actionClear()
    {
        return $this->handleAction('clear');
    }

    public function actionDeleteAll()
    {
        $this->requirePostRequest();

        return $this->handleAction('deleteAll');
    }

    public function actionClearAll()
    {
        $this->requirePostRequest();

        return $this->handleAction('clearAll');
    }


    /**
     * Template layout edit
     */
    public function actionEditFields()
    {
        $variables['title'] = 'Edit List Fields';

        $variables['crumbs'] = array(
            array('label' => Craft::t('Shortlist'), 'url' => UrlHelper::getUrl('shortlist')),
            array('label' => Craft::t('Lists'), 'url' => UrlHelper::getUrl('shortlist/list')),
            array('label' => Craft::t('List Fields'), 'url' => UrlHelper::getUrl('shortlist/list/editFields')),
        );

        $variables['list'] = new Shortlist_ListModel();

        $this->renderTemplate('shortlist/list/_fields', $variables);
    }


    /**
     * Template layout edit
     */
    public function actionSaveLayout()
    {
        $template = new Shortlist_ListModel();

        // Set the field layout
        $fieldLayout = craft()->fields->assembleLayoutFromPost();
        $fieldLayout->type = 'Shortlist_List';
        craft()->fields->deleteLayoutsByType('Shortlist_List');

        if (craft()->fields->saveLayout($fieldLayout))
        {
            craft()->userSession->setNotice(Craft::t('List fields saved.'));
            $this->redirectToPostedUrl();
        }
        else
        {
            craft()->userSession->setError(Craft::t('Couldn’t save list fields.'));
        }


        // Send the feature type back to the template
        craft()->urlManager->setRouteVariables(array(
            'template' => $template
        ));
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
            $this->errorResponse('Couldn\'t complete the list action');
        }

        // We let users create a list and immediately add an item
        if($actionType == 'new') {
            $elementId = craft()->shortlist->getIdForRequest('elementId');
            if($elementId != '') {
                // Try to add the item to the new list
                $item = craft()->shortlist_item->add($elementId, $response['object']->id);
            }
        }

        // Return as appropriate
        if (craft()->request->isAjaxRequest()) {
            $this->returnJson($response);
        } else {
            craft()->shortlist->redirect($response['object']);
        }

    }


    public function actionIndex()
    {
        $this->renderTemplate('shortlist/index');
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
        $criteria = craft()->elements->getCriteria('Shortlist_Item');
        $criteria->listId = $listId;
        $variables['listItems'] = $criteria->find();

        $this->renderTemplate('shortlist/list/_view', $variables);
    }


}