<?php

namespace Craft;

class Shortlist_ItemService extends ShortlistService
{
    private $_itemsByListId = array();
    private $_elementsForItems = array();
    private $_cache;
    private $_cacheElementIds;
    public $response = array();

    public function getItem($elementId)
    {
        if ($this->_cache == null) {
            // No cache - populate it
            $this->populateInfoCache();
        }
        // Get a bare item
        $bareItem = new Shortlist_ItemModel();
        $bareItem->elementId = $elementId;

        $items = array();

        // Get the item for the default list
        $defaultItem = $this->getItemForList($elementId);

        // Now get all the lists for the user. We'll supply a bare item for each list
        $lists = craft()->shortlist_list->getLists();
        foreach ($lists as $list) {
            if (!$list->default) {
                $items[] = $this->getItemForList($elementId, $list);
            }
        }

        $defaultItem->otherLists = $items;

        return $defaultItem;
    }

    private function getItemForList($elementId, Shortlist_ListModel $list = null)
    {
        if ($list == null) {
            // Get for the default list
            $list = $this->_cache['defaultList'];
        }

        $item = null;
        $itemId = $this->checkItemInList($elementId, $list->id);

        if ($itemId === false || !isset($this->_cache['items'][$itemId])) {
            $bare = new Shortlist_ItemModel();
            $bare->elementId = $elementId;
            $bare->listId = $list->id;

            return $bare;
        }

        return $this->_cache['items'][$itemId];
    }

    private function checkItemInList($elementId, $listId)
    {
        $ret = false;
        if (isset($this->_cache['itemsByList'][$listId][$elementId])) {
            $ret = $this->_cache['itemsByList'][$listId][$elementId];
        }

        return $ret;
    }


    public function getItemInfo($elementId = null)
    {
        if ($this->_cache == null) {
            // No cache - populate it
            $this->populateInfoCache();
        }

        // What list are we looking at?
        $listId = 'default'; // @todo - allow this to be overriden

        $ret = array();
        $ret['inList'] = false;
        $ret['add'] = ShortlistHelper::addAction($elementId);
        $ret['remove'] = '#';
        $ret['toggle'] = $ret['add'];

        if (isset($this->_cache['itemsByList'][$listId][$elementId])) {
            $ret['inList'] = true;
            $ret['add'] = '#';
            $ret['remove'] = ShortlistHelper::removeAction($this->_cache['itemsByList'][$listId][$elementId]);
            $ret['toggle'] = $ret['remove'];
        }

        return $ret;
    }


    /**
     * Populate Info Cache
     *
     * Get's all the items and lists for a user
     * and adds them to the static cache for the duration of the request
     */
    private function populateInfoCache()
    {
        if (is_null(craft()->shortlist->user)) {
            // No user for some reason.
            // Populate the caches as blank
            $this->setCacheEmpty();

            return;
        }

        $criteria = craft()->elements->getCriteria('Shortlist_list');
        $criteria->ownerId = craft()->shortlist->user->id;
        $lists = $criteria->find();
        if (empty($lists)) {
            // No lists. Any orphaned items can be ignored
            $this->setCacheEmpty();
        }
        $this->_cache['lists'] = ShortlistHelper::associateResults($lists, 'id');

        $listIds = array();
        foreach ($lists as $list) {
            $listIds[] = $list->id;
        }

        $temp = array();
        $defaultList = new Shortlist_ListModel();
        foreach ($this->_cache['lists'] as $list) {
            if ($list->default) {
                $defaultList = $list;
                $temp['default'] = array();
            }
            $temp[$list->id] = array();
        }

        $this->_cache['defaultListId'] = $defaultList->id;
        $this->_cache['defaultList'] = $defaultList;


        // Get all the items across all this user's lists
        $criteria = craft()->elements->getCriteria('Shortlist_item');
        $criteria->listId = $listIds;
        $items = $criteria->find();

        $this->_cache['items'] = ShortlistHelper::associateResults($items, 'id');

        // Populate a list of the elements that are in items for easier retrieval later
        foreach ($items as $item) {

            $item->inList = true;

            if (!isset($this->_cacheElementIds[$item->elementId])) $this->_cacheElementIds[$item->elementId] = array();
            $this->_cacheElementIds[$item->elementId][$item->listId] = $item->id;

            $temp[$item->listId][$item->elementId] = $item->id;
            if ($item->listId == $defaultList) {
                $temp['default'][$item->elementId] = $item->id;
            }
        }
        $this->_cache['itemsByList'] = $temp;


        return;
    }


    private function setCacheEmpty()
    {
        $this->_cache = array();
        $this->_cacheElementIds = array();
    }


    public function action($actionType, $elementId, $listId = false, $extraData = array())
    {
        // Get the list in question
        $list = new Shortlist_ListModel();

        if (!($listId == false && $actionType == 'remove')) {
            $list = craft()->shortlist_list->getListOrCreate($listId);
            if ($list === false || is_null($list)) {
                // There was a problem getting or creating the list

                //die('handle error action on item - ' . $actionType . ' - ' . $elementId . ' - ' . $listId); // @todo

                if($actionType == 'add') {
                    craft()->shortlist->addError('Couldn\'t find the list to add to');
                } elseif($actionType == 'remove') {
                    craft()->shortlist->addError('Couldn\'t find the list to remove from');
                } else {
                    craft()->shortlist->addError('Couldn\'t find the list for this item');
                }
                return false;

            }
        }

        // Find if we have this item already
        // We need this here so we can change what the action will do
        // based on the existing list content
        $item = $this->findExisting($elementId, $list->id);


        $action = $actionType;
        if ($actionType == 'toggle') {
            if (is_null($item)) {
                $action = 'add';
            } else {
                $action = 'remove';
            }
        } elseif ($actionType == 'add') {
            if (!is_null($item)) {

                // This is a nice and special case
                // The user already has this item in this list and they're adding it again
                // To help them we'll move the item to the very top of the list, add a message
                // and if defined (and possible) we'll increase any qty custom attributes by one
                $actionType = 'promote';
            }
        }


        // Branch now
        // Possible 'remove', 'add', 'promote'.

        // @todo handle restore, demote, move, clear actions
        $response['success'] = false;

        switch ($actionType) {
            case 'add':
                $item = $this->add($elementId, $list->id);
                if ($item == false) {
                    // failed to create or add
                    die('failed to create or add'); // @todo
                }


                $response['object'] = $item;
                $response['objectType'] = 'item';
                $response['verb'] = 'added';
                $response['revert'] = array('verb' => 'remove', 'params' => array('itemId' => $item->id));

                break;
            case 'remove':
                if (is_null($item)) {
                    die('cant remove a null item - ' . $elementId);
                }
                $updatedItem = $this->remove($item);
                if ($updatedItem == false) {
                    // FAiled to remove from list
                    die('failed to remove'); // @todo
                }

                $response['object'] = $updatedItem;
                $response['objectType'] = 'item';
                $response['verb'] = 'removed';
                $response['revert'] = array('verb' => 'restore', 'params' => array('itemId' => $updatedItem->id));

                break;
            case 'promote':
                $updatedItem = $this->promote($elementId, $list->id);
                if ($updatedItem == false) {
                    // Failed to promote in list
                    die('failed to promote');
                }

                $response['object'] = $updatedItem;
                $response['objectType'] = 'item';
                $response['verb'] = 'promoted';
                $response['revert'] = array('verb' => 'demote', 'params' => array('itemId' => $updatedItem->id, 'order' => $item->sortOrder));

                break;
            default:
                die('bad value - ' . $actionType); // @todo
                break;
        }

        // Validate our response
        // Handle any return messages if we got any back
        $response['success'] = true;

        $this->response = $response;
        return true;
    }

    /*
    * Remove by List
     *
     * This 'deletes' all the items from an entire list
     *
    */
    public function removeByList($listId)
    {
        $criteria = craft()->elements->getCriteria('Shortlist_item');
        $criteria->listId = $listId;
        $items = $criteria->find();

        foreach($items as $item)
        {
            $this->remove($item);
        }

        return true;
    }
    /*
    * Remove From List
    *
    * This 'deletes' an item from a list
    * In reality we just mark it as deleted, so we can recover
    * it via an undo operation. This is a short term situation
    * and we use a clear operation to clean out the items marked
    * as deleted async from user requests
    */
    public function remove(Shortlist_ItemModel $itemModel)
    {
        $itemModel->enabled = false;
        craft()->elements->saveElement($itemModel);

        return $itemModel;
    }

    /*
     * Promote In List
     *
     * Promotes an item in a list.
     * This is triggered when an item is attempted to be added to a list
     * that it's already in
     *
     */
    private function promote($elementId, $listId)
    {
        $item = $this->findExisting($elementId, $listId);
        $items = $this->findByList($listId);

        $ordered = array();
        $ordered[] = $item->id;
        foreach ($items as $i) {
            if ($i->id != $item->id) {
                $ordered[] = $i->id;
            }
        }

        $this->reorderItems($ordered);
        $item->sortOrder = '1';

        return $item;
    }


    /*
     * Reorder Items
     *
     * Takes an ordered array of item ids and updates the list order
     *
     */
    private function reorderItems($ordered)
    {
        foreach ($ordered as $itemOrder => $val) {
            $itemRecord = $this->_getItemRecordById($val);
            $itemRecord->sortOrder = $itemOrder + 1;
            $itemRecord->save();

        }

        return true;
    }


    /*
     * Add
     *
     * Adds an item to a list
     *
     * @param $elementId int the Element id
     * @param $listId int the list id to add to
     * @returns Shortlist_ItemModel
     */
    public function add($elementId, $listId)
    {
        $element = craft()->elements->getElementById($elementId);
        if($element === null) return false;

        $itemModel = new Shortlist_ItemModel();
        $itemModel->elementId = $elementId;
        $itemModel->elementType = craft()->elements->getElementTypeById($elementId);
        $itemModel->listId = $listId;

        $extra = array('title' => $element->title);
        $itemModel->setContent($extra);

        if ($itemModel->validate()) {
            // Create the element
            if (craft()->elements->saveElement($itemModel, false)) {
                $record = new Shortlist_ItemRecord();
                $record->setAttributes($itemModel->getAttributes());
                $record->id = $itemModel->id;
                $record->insert();

            } else {
                //$item->addError('general', 'There was a problem creating the list');
                // @todo - add proper error handling
                die('failed');
            }

            craft()->search->indexElementAttributes($itemModel);

            return $itemModel;

        } else {
            die('failed to validate');
        }


    }

    /**
     * Find Existing
     *
     * Gets a list item from a list based on the elementId and listId
     * If not found will return null, otherwise the itemModel
     */
    private function findExisting($elementId, $listId)
    {
        $criteria = craft()->elements->getCriteria('Shortlist_item');
        $criteria->id = $elementId;
        $item = $criteria->first();
        if($item != null) return $item;


        // The inbound elementId, might actually be the id of the shortlist_item element, so allow that too
        $criteria = craft()->elements->getCriteria('Shortlist_item');
        $criteria->elementId = $elementId;
        $criteria->listId = $listId;
        $item = $criteria->first();

        return $item;
    }


    /*
     * Clear By List
     *
     * Clears all the items in a list
     */
    public function clearByList($listId)
    {
        $items = $this->findByList($listId);

        foreach($items as $item) {
            $this->remove($item);
        }

        return true;
    }

    /*
     * Find By List
     *
     * Gets an array of items for a specific list
     *
     * @return array()
     */
    public function findByList($listId)
    {
        if (!isset($this->_itemsByListId[$listId])) {

            $criteria = craft()->elements->getCriteria('Shortlist_item');
            $criteria->listId = $listId;
            $criteria->order = 'sortOrder asc, dateCreated asc';
            $items = $criteria->find();

            $this->_itemsByListId[$listId] = $items;
        }

        return $this->_itemsByListId[$listId];
    }

    /*
     * Find Parent Element
     *
     * Gets the parent element for a list item
     *
     * @return ElementModel
     */
    public function findParentElement($elementId)
    {
        if (!isset($this->_elementsForItems[$elementId])) {

            $element = craft()->elements->getElementById($elementId);

            $this->_elementsForItems[$elementId] = $element;
        }

        return $this->_elementsForItems[$elementId];
    }


    /**
     * Gets an items's record.
     *
     * @param int $itemId
     *
     * @throws Exception
     * @return Shortlist_ItemRecord
     */
    private function _getItemRecordById($itemId = null)
    {
        if ($itemId) {
            $itemRecord = Shortlist_ItemRecord::model()->findById($itemId);

            if (!$itemRecord) {
                throw new Exception(Craft::t('No item exists with the ID “{id}”', array('id' => $itemId)));
            }
        } else {
            $itemRecord = new Shortlist_ItemRecord();
        }

        return $itemRecord;
    }
}
