<?php

namespace Craft;

class ShortlistVariable
{

    public function item($elementId = null)
    {
        //$actions = craft()->shortlist_item->getItemInfo($elementId);
       //return $actions;

        $itemElement = craft()->shortlist_item->getItem($elementId);
        return $itemElement;
    }


    public function lists($criteria = null)
    {
        if(is_null($criteria)) {
            $criteria = craft()->elements->getCriteria('shortlist_list');
        }

        // Limit to this user
        $criteria->ownerId = craft()->shortlist->user->id;
        return $criteria->find();
    }

}
