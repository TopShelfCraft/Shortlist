<?php
namespace Craft;

class Shortlist_ItemController extends ShortlistController
{
    protected $allowAnonymous = true;

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
        $this->handleAction('add');
    }

    public function actionRemove()
    {
        $this->handleAction('remove');
    }

    public function actionToggle()
    {
        $this->handleAction('toggle');
    }


    /**
     * Handle Action
     *
     * Does all the leg work for the common actions
     */
    public function handleAction($actionType)
    {
        // Collect the info
        // at the very least we need an item id
        $itemId = craft()->shortlist->getIdForRequest('itemId,id');
        if ($itemId == false) {
            // Return an error message
            die('no id'); // @todo
        }

        // Validate this id is a real element id
        $element = craft()->elements->getElementById($itemId);
        if (is_null($element)) {
            die('not a real element');
            // @todo
        }

        // Get the list for the request
        // We might have a list_id on the request.
        // don't worry too much if we haven't got it though -
        // we'll use the default list if it doesn't exist, or worst
        // case we'll create a new list on the action if we need to
        $listId = craft()->shortlist->getIdForRequest('listId', 1);

        // Get any extra values
        $extra = array(); // @todo

        // Pass to the service to do the leg work
        $response = craft()->shortlist_item->action($actionType, $itemId, $listId, $extra);


        if($response === true) {
            // Return as appropriate
            if (craft()->request->isAjaxRequest()) {
                $this->returnJson($response);
            } else {
                craft()->shortlist->redirect($response['object']);
            }
        }


        $this->returnError();
    }


    private function getIdForRequest($name = 'itemId,id', $offset = 0)
    {
        $id = false;
        // Explode our name if required
        $names = explode(',', $name);

        foreach ($names as $n) {
            if ($id === false || $id == '' || is_null($id)) {
                if (craft()->request->getPost($n)) {
                    $id = craft()->request->getPost($n);
                } elseif (craft()->request->getQuery($n)) {
                    $id = craft()->request->getQuery($n);
                }
            }
        }

        if ($id === false || $id == '' || is_null($id)) {
            // Try to extract it from the request segments
            $count = 0;
            foreach (craft()->request->getSegments() as $pos => $segment) {
                if (is_numeric($segment) && !is_numeric($id)) {
                    $count++;
                    if ($count > $offset) {
                        $id = $segment;
                    }
                }
            }
        }

        if (is_numeric($id)) return $id;

        return false;
    }


    public function actionIndex()
    {
        $this->requireAdmin();
        $this->renderTemplate('shortlist/item/index');
    }

    public function actionView(array $variables = array())
    {
        $this->renderTemplate('shortlist/item/index');

        /*
        $this->requireAdmin();

        $listId = $variables['itemId'];
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

        $this->renderTemplate('shortlist/item/_view', $variables);*/
    }



}