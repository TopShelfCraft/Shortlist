<?php

namespace Craft;

class Shortlist_ItemService extends BaseApplicationComponent
{
	private $_itemsByListId = array();
	private $_elementsForItems = array();
	private $_user = null;
	private $_cache;
	private $_cacheElementIds;




	public function getItemInfo($elementId = null)
	{
		if($this->_user == null) $this->_user = new Shortlist_UserModel();

		if($this->_cache == null) {
			// No cache - populate it
			$this->populateInfoCache();
		}

		$ret = array();
		$ret['inList'] = false;
		$ret['add'] = ShortlistHelper::addAction($elementId);
		$ret['remove'] = '#';
		$ret['toggle'] = $ret['add'];

		if(isset($this->_cacheElementIds[$elementId])) {
			$ret['inList'] = true;
			$ret['add'] = '#';
			$ret['remove'] = ShortlistHelper::removeAction(current($this->_cacheElementIds[$elementId])); // @todo - remove this abguity, we shouldn't rely on this multi state, but instead get the default action if not specified
			$ret['toggle'] = $ret['remove'];

			// @todo - add more dynamic data here

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
		if(is_null($this->_user)) {
			// No user for some reason.
			// Populate the caches as blank
			$this->setCacheEmpty();
		}

		$lists = Shortlist_ListRecord::model()->findAllByAttributes(array('ownerId' => $this->_user->id));
		if(empty($lists)) {
			// No lists. Any orphaned items can be ignored
			$this->setCacheEmpty();
		}
		$this->_cache['lists'] = ShortlistHelper::associateResults($lists, 'id');

		$listIds = array();
		foreach($lists as $list) {
			$listIds[] = $list->id;
		}

		$items = Shortlist_ItemRecord::model()->findAllByAttributes(array('listId' => $listIds));
		$this->_cache['items'] = ShortlistHelper::associateResults($items, 'id');

		// Populate a list of the elements that are in items for easier retrival later

		foreach($items as $item) {
			if(!isset($this->_cacheElementIds[$item->elementId])) $this->_cacheElementIds[$item->elementId] = array();
			$this->_cacheElementIds[$item->elementId][$item->listId] = $item->id;
		}


		// @todo we should really return the element models not the normal models so all the extra tags will work later
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
		$list = craft()->shortlist_list->getListOrCreate($listId);
		if ($list === false || is_null($list)) {
			// There was a problem getting or creating the list
			die('handle error creating list'); // @todo
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
				$action = 'promote';
			}
		}

		// Branch now
		// Possible 'remove', 'add', 'promote'.

		// @todo handle restore, demote, move, clear actions
		$response['success'] = false;

		switch ($actionType) {
			case 'add':
				$item = $this->createAddToList($elementId, $list->id);
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
					die('cant remove a null item');
				}
				$updatedItem = $this->removeFromList($item, $list->id);
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
				$updatedItem = $this->promoteInList($elementId, $list->id);
				if ($updatedItem == false) {
					// Failed to promote in list
					die('failed to promote');
				}

				$response['object'] = $updatedItem;
				$response['objectType'] = 'item';
				$response['verb'] = 'promoted';
				$response['revert'] = array('verb' => 'demote', 'params' => array('itemId' => $updatedItem->id, 'order' => $item->order));

				break;
			default:
				die('bad value - ' . $actionType); // @todo
				break;
		}

		// Validate our response
		// Handle any return messages if we got any back
		$response['success'] = true;

		return $response;
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
    private function removeFromList(Shortlist_ItemRecord $itemRecord, $listId)
    {
        $itemRecord->deleted = true;
        $itemRecord->update();

        // Return the updated model
        $itemModel = Shortlist_ItemRecord::model()->findByAttributes(array('id' => $itemRecord->id, 'deleted' => true));
        return $itemModel;
    }

    private function createAddToList($elementId, $listId)
    {
    	$itemModel = new Shortlist_ItemModel();
    	$itemModel->elementId = $elementId;
		$itemModel->elementType = craft()->elements->getElementTypeById($elementId);
    	$itemModel->listId = $listId;

		if($itemModel->validate()) {
	        // Create the element
	        if (craft()->elements->saveElement($itemModel, false))
	        {
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
			die('<pre>'.print_R($itemModel,1));
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
    	return Shortlist_ItemRecord::model()->findByAttributes( array('id' => $elementId, 'listId' => $listId) );
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
		if(!isset($this->_itemsByListId[$listId])) {

			$records = Shortlist_ItemRecord::model()->findAllByAttributes(array('listId' => $listId));
			$items = Shortlist_ItemModel::populateModels($records);

			// While we have them, we'll get all the elements for this list
            // Saving multiple queries down the road
            $elementIds = array();
            foreach($items as $item) {
                $elementIds[$item->elementType][] = $item->elementId;
            }

			$this->_getElements($elementIds);
			$this->_itemsByListId[$listId] = $items;
		}

		return $this->_itemsByListId[$listId];
	}

	/*
	 * Get Elements
	 *
	 * Gets the elements from a set of elementIds
	 * Note - these elements might be across different types
	 * and we don't discriminate, so we first have to group them
	 * by type to be able to play nice with the internal criteria
	 * restrictions. We'll throw these into the cache so we've
	 * got them around for future requests
	 */
	private function _getElements($elementIds = array())
	{
		if(empty($elementIds)) return;

		foreach($elementIds as $elementType => $ids) {
			$criteria = craft()->elements->getCriteria($elementType);
			$criteria->ids = $ids;

			$ele = $criteria->find();

			foreach($ele as $e) {
				$this->_elementsForItems[$e->id] = $e;
			}
		}

		return;
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
		if(!isset($this->_elementsForItems[$elementId])) {

			$element = craft()->elements->getElementById($elementId);

			$this->_elementsForItems[$elementId] = $element;
		}

		return $this->_elementsForItems[$elementId];
	}
}
