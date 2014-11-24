<?php

namespace Craft;

class Shortlist_ListService extends BaseApplicationComponent
{

    public function getListOrCreate($listId)
    {
    	if($listId === false) {
    		// create a list
    		return $this->createList();
    	}

    	die('get existing list');
    }


    public function createList()
    {
    	$listModel = new Shortlist_ListModel();
    	// Get the defaults // @todo
    	$listModel->name = 'default_listname';
    	$listModel->title = 'New List';
		$listModel->shareSlug = strtolower(StringHelper::randomString(18));
		$listModel->slug = 'default-slug';
		$listModel->userSlug = 'someuser-slug';

        $user = craft()->userSession->getUser();
        if ($user) {
        	$listModel->ownerId = $user->id;
        	$listModel->ownerType = Shortlist_OwnerType::Member;
        } else {
        	$listModel->ownerId = StringHelper::UUID();
        	$listModel->ownerType  = Shortlist_OwnerType::Guest;
        }


		if($listModel->validate()) {
	        // Create the element
	        if (craft()->elements->saveElement($listModel, false))
	        {
	            $record = new Shortlist_ListRecord();
	            $record->setAttributes($listModel->getAttributes());
	            $record->id = $listModel->id;
	            $record->insert();
	        } else {
	        	$listModel->addError('general', 'There was a problem creating the list');
	        }

	        craft()->search->indexElementAttributes($listModel);

	        return $listModel;

		} else {
			if(!empty(craft()->shortlist_list->errors)) {
				foreach(craft()->shortlist_list->errors as $error) {
					$listModel->addError('general', $error);
				}
			} else {
				$listModel->addError('general', 'There was a problem with creating the list');
			}
			die('<pre>'.print_R($listModel,1));
			die('invalid');
		}

		return null;

    }

}

