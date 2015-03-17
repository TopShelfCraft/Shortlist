<?php

namespace Craft;

class Shortlist_ListService extends ShortlistService
{

    public function action($actionType, $listId = false, $extraData = array())
    {
        $response['success'] = false;
        $response['object'] = '';
        $response['objectType'] = '';
        $response['verb'] = 'failed';
        $response['revert'] = array('verb' => '', 'params' => '');

        switch ($actionType) {
            case 'new' : {
                $list = $this->create(null, $extraData);

                $response['object'] = $list;
                $response['objectType'] = 'list';
                $response['verb'] = 'created';
                $response['revert'] = array('verb' => 'remove', 'params' => array('listId' => $list->id));
                $response['success'] = true;

                break;
            }
            case 'remove' : {
                $list = $this->remove($listId, $extraData);

                $response['object'] = $list;
                $response['objectType'] = 'list';
                $response['verb'] = 'deleted';
                $response['revert'] = false; //array('verb' => 'remove', 'params' => array('listId' => $list->id));
                $response['success'] = true;

                break;
            }
            case 'makeDefault' : {
                $list = $this->makeDefault($listId, $extraData);

                $response['object'] = $list;
                $response['objectType'] = 'list';
                $response['verb'] = 'defaulted';
                $response['revert'] = false; //array('verb' => 'remove', 'params' => array('listId' => $list->id));
                $response['success'] = true;

                break;
            }
            case 'clear' : {
                $state = $this->clear($listId);

                $response['object'] = null;
                $response['objectType'] = 'list';
                $response['verb'] = 'cleared';
                $response['revert'] = false; //array('verb' => 'remove', 'params' => array('listId' => $list->id));
                $response['success'] = true;

                break;
            }
            case 'deleteAll' : {
                $state = $this->removeAll();

                $response['object'] = null;
                $response['objectType'] = '';
                $response['verb'] = 'removed';
                $response['revert'] = false; //array('verb' => 'remove', 'params' => array('listId' => $list->id));
                $response['success'] = true;

                break;
            }
            case 'clearAll' : {
                $state = $this->clearAll();

                $response['object'] = null;
                $response['objectType'] = '';
                $response['verb'] = 'cleared';
                $response['revert'] = false; //array('verb' => 'remove', 'params' => array('listId' => $list->id));
                $response['success'] = true;

                break;
            }
            case 'update' : {
                $state = $this->update($listId, $extraData);

                $response['object'] = null;
                $response['objectType'] = '';
                $response['verb'] = 'updated';
                $response['revert'] = false; //array('verb' => 'remove', 'params' => array('listId' => $list->id));
                $response['success'] = true;

            }
            default : {

                craft()->shortlist->addError('Sorry, unknown list action type');
                return false;

                break;
            }

        }

        return $response;
    }


    public function update($listId, $data)
    {
        // @todo
    }
    /*
     * Remove All
     *
     * Deletes all of a user's lists
     */
    public function removeAll()
    {
        $criteria = craft()->elements->getCriteria('Shortlist_List');
        $criteria->ownerId = craft()->shortlist->user->id;
        $lists = $criteria->find();

        foreach ($lists as $list) {
            craft()->shortlist_item->removeByList($list->id);
            $list->enabled = false;
            craft()->elements->saveElement($list);
        }

        return true;
    }

    /*
     * Clear All
     *
     * Clears all of a user's lists
     */
    public function clearAll()
    {
        $criteria = craft()->elements->getCriteria('Shortlist_List');
        $criteria->ownerId = craft()->shortlist->user->id;
        $lists = $criteria->find();

        foreach ($lists as $list) {
            $ret = craft()->shortlist_item->clearByList($list->id);
        }

        return true;

    }

    /*
     * Clear
     *
     * Clears out a list, removing all the items within
     */
    public function clear($listId)
    {
        $criteria = craft()->elements->getCriteria('Shortlist_List');
        $criteria->ownerId = craft()->shortlist->user->id;
        $criteria->listId = $listId;
        $list = $criteria->first();

        if (empty($list)) {
            // Not a valid list, or not the owner
            // @todo log error message
            return false;
        }

        // Get all the items in this list and remove each
        $ret = craft()->shortlist_item->clearByList($listId);

        return $ret;
    }

    /*
     * Make Default
     *
     * Makes a list the default for a specific user
     * In the same action undefaults the previously default list
     *
     * @returns bool
     */
    private function makeDefault($listId, $extraData = array())
    {
        // Check this is a valid list to remove
        // both that it exists, and the current user is the owner of said list
        $criteria = craft()->elements->getCriteria('Shortlist_List');
        $criteria->ownerId = craft()->shortlist->user->id;

        // Get all the user's lists
        $allLists = $criteria->find();

        // Now just this list
        $criteria->id = $listId;
        $criteria->enabled = false; // Can't make a deleted list the default
        $list = $criteria->first();


        if (is_null($list)) {
            // @todo, add a message
            return false; // not a valid list or not list owner
        }

        // Make all the lists undefault before making the current list the default
        $listIds = array();
        foreach ($allLists as $l) {
            $listIds[] = $l->id;
        }
        $this->makeUndefault($listIds);

        // Now make our new default list
       // $list->default = true;
       // craft()->elements->saveElement($list);

        $listRecord = Shortlist_ListRecord::model()->findByAttributes(array('id' => $list->id));
        $listRecord->default = true;
        $listRecord->update();

        return true;
    }

    /*
     * Remove List
     *
     * Removes a list if the user is the owner
     * In reality, simply sets the list state to deleted to allow reverts
     * actual deletion happens later
     *
     * @returns bool
     */
    public function remove($listId, $extraData = array())
    {
        // Check this is a valid list to remove
        // both that it exists, and the current user is the owner of said list
        $list = $this->getListById($listId);

        if (is_null($list)) {
            // @todo, add a message
            return false; // not a valid list or not list owner
        }

        // Also delete all the sub-items
        foreach ($list->items() as $item) {
            craft()->shortlist_item->action('remove', $item->id);
        }

        $list->enabled = false;
        craft()->elements->saveElement($list);

        // Make a new default list
        $this->verifyDefaults();

        return true;
    }


    /*
     * Verify Defaults
     *
     * Loops over a user's full set of (open) lists, and
     * makes sure we have a single default list that is enabled
     *
     * @return null
     */
    private function verifyDefaults()
    {
        $lists = $this->getLists();
        $defaultList = null;
        $needsClean = false;
        $firstList = null;

        foreach ($lists as $list) {
            // Pull out the most recently updated list just in case
            if ($firstList == null) {
                $firstList = $list;
            } else {
                if ($firstList->dateUpdated < $list->dateUpdated) {
                    $firstList = $list;
                }
            }

            if ($list->default) {
                if ($defaultList != null) {
                    $needsClean = true;

                    // Default to the most recently updated list as the default
                    if ($defaultList->dateUpdated < $list->dateUpdated) {
                        $defaultList = $list;
                    }
                }
            }
        }

        if ($defaultList == null) {
            // We don't have a default. Pick the most recently updated one
            if ($firstList == null) {
                // Welp. can't do nada
                return;
            }
            $needsClean = true;
            $defaultList = $firstList;
        }

        if ($needsClean) {
            $this->makeDefault($defaultList->id);
        }

        return;
    }


    /**
     * Get Lists for User
     *
     * Gets all the lists for a user.
     * Unless specified and the request is from the CP
     * will limit to the current user only
     *
     * @param null $userId
     * @return array
     */
    public function getLists($userId = null)
    {
        $criteria = craft()->elements->getCriteria('Shortlist_List');
        $criteria->ownerId = craft()->shortlist->user->id;

        // Useful for later impersonation in the CP
        if (!(craft()->request->isCpRequest() && $userId !== null)) {
            $criteria->ownerId = craft()->shortlist->user->id;
        }

        return $criteria->find();
    }

    /*
     * Get List By ID
     *
     * Get's a specific list by id. If this is a CP request
     * This will return any valid list. If it's a normal request
     * this is limited to both the user and state
     *
     * @returns List
    */
    public function getListById($listId, $limitToUser = true)
    {
        if (craft()->request->isCpRequest()) $limitToUser = false;

        $criteria = craft()->elements->getCriteria('Shortlist_List');
        $criteria->id = $listId;

        if ($limitToUser) {
            $criteria->ownerId = craft()->shortlist->user->id;
            $criteria->enabled = true;
        }

        return $criteria->first();
    }

    /*
     * Get List By ID
     *
     * Get's a specific list by id. If this is a CP request
     * This will return any valid list. If it's a normal request
     * this is limited to both the user and state
     *
     * @returns List
    */
    public function getListByIdOrBare($listId)
    {
        $list = $this->getListById($listId);

        if ($list == null) {
            $list = new Shortlist_ListModel();
        }

        return $list;
    }

    public function getListOrCreate($listId)
    {
        if ($listId === false) {
            // Try to get the user's default list
            $defaultList = $this->getDefaultList();
            if($defaultList != null) {
                return $defaultList;
            }
            // create a new list
            return $this->create();
        }

        return $this->getListById($listId);
    }


    public function getDefaultList()
    {
        $criteria = craft()->elements->getCriteria('Shortlist_List');
        $criteria->ownerId = craft()->shortlist->user->id;
        $criteria->default = true;
        $list = $criteria->first();

        return $list;
    }


    private function makeUndefault($listIds = array())
    {
        if (!is_array($listIds)) {
            $listIds[] = $listIds;
        }

        if (empty($listIds)) return;

        // Do a direct query so we don't hit the dateUpdated record value
        $sql = 'UPDATE ' . DBHelper::addTablePrefix('shortlist_list') . ' s SET s.default = false WHERE s.id IN (' . implode(', ', $listIds) . ')';
        $query = craft()->db->createCommand($sql)->execute();

        return;
    }

    public function create($makeDefault = true, $extraData = array())
    {
        if (!is_bool($makeDefault)) $makeDefault = true;

        $settings = craft()->plugins->getPlugin('shortlist')->getSettings();

        $listModel = new Shortlist_ListModel();
        $listModel->shareSlug = strtolower(StringHelper::randomString(18));
        $listModel->ownerId = $this->user->id;
        $listModel->ownerType = $this->user->type;


        // Assign the extra data if possible
        $assignable = array('listTitle' => 'title', 'listSlug' => 'slug');
        $extra = array();
        $extra['title'] = $settings->defaultListTitle;
        foreach ($assignable as $key => $val) {
            if (isset($extraData[$key]) && $extraData[$key] != '') {
                $extra[$val] = $extraData[$key];
            }
        }
        $listModel->setContent($extra);


        if ($listModel->validate()) {
            // Create the element
            if (craft()->elements->saveElement($listModel, false)) {
                $record = new Shortlist_ListRecord();
                $record->setAttributes($listModel->getAttributes());
                $record->id = $listModel->id;
                $record->insert();
            } else {
                $listModel->addError('general', 'There was a problem creating the list');
            }

            craft()->search->indexElementAttributes($listModel);


            if ($makeDefault) {
                // We need to unset all other lists for this to be valid
                $this->makeDefault($listModel->id);
            }

            return $listModel;

        } else {
            $listModel->addError('general', 'There was a problem with creating the list');
            return false;
        }

        return null;

    }

}

