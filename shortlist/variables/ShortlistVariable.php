<?php

namespace Craft;

class ShortlistVariable
{

    public function newListActionUrl($options = array())
    {
        return ShortlistHelper::newListAction($options);
    }

    public function item($elementId = null)
    {
        $itemElement = craft()->shortlist_item->getItem($elementId);
        return $itemElement;
    }

    public function itemCount($criteria = null)
    {
        if (is_null($criteria)) {
            $criteria = craft()->elements->getCriteria('Shortlist_Item');
        }

        $criteria->ownerId = craft()->shortlist->user->id;
        return $criteria->count();
    }

    public function itemsForElements($elements = array())
    {
        $criteria = craft()->elements->getCriteria('Shortlist_Item');

        $elementIds = array();
        foreach($elements as $element) {
            $elementIds[] = $element->id;
        }

        $criteria->elementId = $elementIds;
        $criteria->ownerId = craft()->shortlist->user->id;
        return $criteria->find();
    }

    public function lists($criteria = null)
    {
        if (is_null($criteria)) {
            $criteria = craft()->elements->getCriteria('Shortlist_List');
        }

        $criteria->ownerId = craft()->shortlist->user->id;
        return $criteria->find();
    }

    public function error()
    {
        $error = craft()->userSession->getFlash('error', null, false);
        $charset = craft()->templates->getTwig()->getCharset();

        if (!is_null($error)) {
            return new \Twig_Markup($error, $charset);
        }

        return null;
    }

    public function searchListsByFields($searchArray, $limitToOwner = true)
    {
        $criteria = craft()->elements->getCriteria('Shortlist_List');

        if($limitToOwner !== false) {
            $criteria->ownerId = craft()->shortlist->user->id;
        }

        foreach($searchArray as $key => $val) {
            $criteria->$key = $val;
        }

        return $criteria->find();
    }

    public function getListById($listId, $limitToOwner = true)
    {
        $criteria = craft()->elements->getCriteria('Shortlist_List');

        if($limitToOwner !== false) {
            $criteria->ownerId = craft()->shortlist->user->id;
        }

        $criteria->id = $listId;

        return $criteria->first();
    }

    public function getSharedList($listShareSlug)
    {
        $criteria = craft()->elements->getCriteria('Shortlist_List');

        $criteria->shareSlug = $listShareSlug;

        return $criteria->first();
    }



    public function assignSuperUserForList($listId)
    {
        return craft()->shortlist_list->assignSuperUserForList($listId);
    }


}
