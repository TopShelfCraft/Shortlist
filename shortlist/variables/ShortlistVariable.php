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

        $criteria->status = 'disabled';

        //var_dump($criteria);

        return $criteria->find();
    }

}
