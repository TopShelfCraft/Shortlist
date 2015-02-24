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
            case 'new' :

                $list = $this->createList(null, $extraData);

                $response['object'] = $list;
                $response['objectType'] = 'list';
                $response['verb'] = 'created';
                $response['revert'] = array('verb' => 'remove', 'params' => array('listId' => $list->id));
                $response['success'] = true;

                break;
            case 'remove' :

                $list = $this->removeList($listId, $extraData);

                $response['object'] = $list;
                $response['objectType'] = 'list';
                $response['verb'] = 'deleted';
                $response['revert'] = false; //array('verb' => 'remove', 'params' => array('listId' => $list->id));
                $response['success'] = true;

                break;
            case 'makeDefault' :

                $list = $this->makeDefault($listId, $extraData);

                $response['object'] = $list;
                $response['objectType'] = 'list';
                $response['verb'] = 'defaulted';
                $response['revert'] = false; //array('verb' => 'remove', 'params' => array('listId' => $list->id));
                $response['success'] = true;

                break;

            default :

                die('Unknown - ' . $actionType);
                // @todo
                break;

        }

        return $response;
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
        $criteria = craft()->elements->getCriteria('shortlist_list');
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
    private function removeList($listId, $extraData = array())
    {
        // Check this is a valid list to remove
        // both that it exists, and the current user is the owner of said list
        $criteria = craft()->elements->getCriteria('shortlist_list');
        $criteria->id = $listId;
        $criteria->ownerId = craft()->shortlist->user->id;
        $criteria->enabled = true; // Can't delete a deleted thing silly.

        $list = $criteria->first();

        if (is_null($list)) {
            // @todo, add a message
            return false; // not a valid list or not list owner
        }

        // Also delete all the sub-items
        foreach ($list->items() as $item) {
            craft()->shortlist_item->action('remove', $item->id);
        }

        $listRecord = Shortlist_ListRecord::model()->findByAttributes(array('id' => $list->id));
        $listRecord->deleted = true;
        $listRecord->update();

        // Return the updated model
        $listModel = Shortlist_ListRecord::model()->findByAttributes(array('id' => $listRecord->id, 'deleted' => true));

        $list->enabled = false;
        craft()->elements->saveElement($list);

        // Make a new default list
        // @todo - make a new list the default if this was the default

        return true;
    }


    public function getListById($listId)
    {
        $criteria = craft()->elements->getCriteria('shortlist_list');
        $criteria->id = $listId;

        return $criteria->first();
    }

    public function getListOrCreate($listId)
    {
        if ($listId === false) {
            // Try to get the user's default list
            $defaultList = $this->getDefaultList();
            if (is_null($defaultList)) {
                // create a new list
                return $this->createList();
            }

            return $defaultList;
        }

        die('get specific list by id');
    }

    public function getDefaultList()
    {
        // @todo we could check the global settings and setup default defined lists here maybe?
        $lists = Shortlist_ListRecord::model()
            ->findAllByAttributes(array('ownerId' => craft()->shortlist->user->id, 'deleted' => false), array('order' => 'dateUpdated DESC'));

        if (empty($lists)) return null; // No default list defined

        // More than one default
        // Fix this now. Just make the most recently updated the default
        $list = null;
        if (count($lists) > 1) {
            // the first is the most recent, skip it
            $list = array_shift($lists);

            // Now reset all the other lists to be non-default
            $listIds = array();
            foreach ($lists as $unsetList) {
                $listIds[] = $unsetList->id;
            }

            $this->makeUndefault($listIds);
        } else {
            $list = current($lists);
        }


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

    public function createList($makeDefault = true, $extraData = array())
    {
        if (!is_bool($makeDefault)) $makeDefault = true;

        $settings = craft()->plugins->getPlugin('shortlist')->getSettings();

        $listModel = new Shortlist_ListModel();
        $listModel->name = $settings->defaultListName;
        $listModel->title = $settings->defaultListTitle;
        $listModel->shareSlug = strtolower(StringHelper::randomString(18));
        $listModel->slug = $settings->defaultListSlug;
        $listModel->userSlug = 'someuser-slug';
        $listModel->default = false; // We'll set this at the end when we know if it's succeeded
        $listModel->ownerId = craft()->shortlist->user->id;
        $listModel->ownerType = craft()->shortlist->user->type;
        $listModel->deleted = false;


        // Assign the extra data if possible
        $assignable = array('listTitle' => 'title', 'listSlug' => 'slug', 'listName' => 'name');
        foreach ($assignable as $key => $val) {
            if (isset($extraData[$key]) && $extraData[$key] != '') {
                $listModel->$val = $extraData[$key];
            }
        }

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

            return $listModel;

        } else {
            $listModel->addError('general', 'There was a problem with creating the list');

            echo('problem creating');
            die('<pre>' . print_R($listModel, 1));
            die('invalid');
        }


        if ($makeDefault) {
            // We need to unset all other lists for this to be valid
            $this->makeDefault($listModel->id);
        }

        return null;

    }


}

