<?php

namespace Craft;

class ShortlistVariable
{

    public function item($elementId = null)
    {
        return craft()->shortlist_item->getItemInfo($elementId);
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
