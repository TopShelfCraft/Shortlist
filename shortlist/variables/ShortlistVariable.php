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
        return craft()->shortlist->getItemInfo($elementId);
    }


    public function setProtected($values)
    {
        return implode('-',array_keys($values));
    }

}
