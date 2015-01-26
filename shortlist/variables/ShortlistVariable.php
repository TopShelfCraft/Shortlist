<?php

namespace Craft;

class ShortlistVariable
{

    /**
     * Returns an ElementCriteriaModel set to find charges.
     *
     * @param array|null $criteria
     * @return ElementCriteriaModel
     */
    /*    public function charges($criteria = null)
        {
            return craft()->elements->getCriteria('Charge', $criteria);
        }*/


    public function item($elementId = null)
    {
        return craft()->shortlist_item->getItemInfo($elementId);
    }


    public function lists($criteria = null)
    {
        return craft()->elements->getCriteria('Shortlist_list', $criteria);
    }

}
