<?php

namespace Craft;

class Shortlist_ItemService extends BaseApplicationComponent
{

    public function action($actionType, $elementId, $listId = false, $extraData = array())
    {
        // Get the list in question
        $list = craft()->shortlist_list->getListOrCreate($listId);
        if($list === false || is_null($list)) {
        	// There was a problem getting or creating the list
        	die('handle error creating list'); // @todo
        }

        // Find if we have this item already
        // We need this here so we can change what the action will do
        // based on the existing list content
        $item = $this->findExisting($elementId, $list->id);

        $action = $actionType;
        if($actionType == 'toggle') {
        	if(is_null($item)) {
        		$action = 'add';
        	} else {
        		$action = 'remove';
        	}
        } elseif($actionType == 'add') {
        	if(!is_null($item)) {

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

        switch($actionType) {
        	case 'add':
        		$item = $this->createAddToList($elementId, $list->id);
        		if($item == false) {
        			// failed to create or add
        			die('failed to create or add'); // @todo
        		}


        		$response['object'] = $item;
        		$response['objectType'] = 'item';
        		$response['verb'] = 'added';
        		$response['revert'] = array('verb' => 'remove', 'params' => array('itemId' => $item->id));

	        	break;
        	case 'remove':
        		$updatedItem = $this->removeFromList($elementId, $list->id);
        		if($updatedItem == false) {
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
        		if($updatedItem == false) {
        			// Failed to promote in list
        			die('failed to promote');
        		}

        		$response['object'] = $updatedItem;
        		$response['objectType'] = 'item';
        		$response['verb'] = 'promoted';
        		$response['revert'] = array('verb' => 'demote', 'params' => array('itemId' => $updatedItem->id, 'order' => $item->order));

	        	break;
        	default:
        		die('bad value - '.$actionType); // @todo
		    	break;
        }

        // Validate our response
        // Handle any return messages if we got any back
        $response['success'] = true;

        return $response;
    }


    private function createAddToList($elementId, $listId)
    {
    	$itemModel = new Shortlist_ItemModel();
    	$itemModel->elementId = $elementId;
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

}
