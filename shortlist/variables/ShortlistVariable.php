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
        $items = $criteria->find();

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
}
