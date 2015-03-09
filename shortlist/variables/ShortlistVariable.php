<?php

namespace Craft;

class ShortlistVariable
{

    public function newList($options = array())
    {
        return ShortlistHelper::newListAction($options);
    }

    public function item($elementId = null)
    {
        $itemElement = craft()->shortlist_item->getItem($elementId);
        return $itemElement;
    }


    public function lists($criteria = null)
    {
        if(is_null($criteria)) {
            $criteria = craft()->elements->getCriteria('Shortlist_list');
        }

        $criteria->ownerId = craft()->shortlist->user->id;
        return $criteria->find();
    }

}
