<?php
namespace Craft;

class ShortlistHelper
{

    public static function newListAction($options = array())
    {
        $params['return'] = craft()->request->getUrl();
        if(isset($options['return'])) $params['return'] = $options['return'];

        return UrlHelper::getActionUrl('shortlist/list/new', $params);
    }


    public static function toggleAction($elementId, $listId = null, $options = array())
    {
        $params = array();
        $params['id'] = $elementId;
        $params['return'] = craft()->request->getUrl();
        if($listId !== null) $params['listId'] = $listId;
        if(isset($options['return'])) $params['return'] = $options['return'];

        return UrlHelper::getActionUrl('shortlist/item/toggle', $params);
    }

    public static function addAction($elementId, $listId = null, $options = array())
    {
        $params = array();
        $params['id'] = $elementId;
        $params['return'] = craft()->request->getUrl();
        if($listId !== null) $params['listId'] = $listId;
        if(isset($options['return'])) $params['return'] = $options['return'];

        return UrlHelper::getActionUrl('shortlist/item/add', $params);
    }

    public static function removeAction($itemId, $listId = null, $options = array())
    {
        $params = array();
        $params['itemId'] = $itemId;
        $params['return'] = craft()->request->getUrl();
        if($listId !== null) $params['listId'] = $listId;
        if(isset($options['return'])) $params['return'] = $options['return'];

        return UrlHelper::getActionUrl('shortlist/item/remove', $params);
    }

    public static function associateResults($resultset, $key, $sort = false)
    {
        $array = array();

        foreach ($resultset AS $row) {
            $array[$row->$key] = $row;
        }

        if ($sort === true) {
            ksort($array);
        }

        return $array;
    }
}


