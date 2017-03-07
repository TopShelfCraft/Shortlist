<?php
namespace Craft;

class ShortlistHelper
{



    public static function removeListAction($listId, $options = array())
    {
        $params['listId'] = $listId;
        $params['return'] = craft()->request->getUrl();
        if(isset($options['return'])) $params['return'] = $options['return'];

        return UrlHelper::getActionUrl('shortlist/list/delete', $params);
    }


    public static function clearListAction($listId, $options = array())
    {
        $params['listId'] = $listId;
        $params['return'] = craft()->request->getUrl();
        if(isset($options['return'])) $params['return'] = $options['return'];

        return UrlHelper::getActionUrl('shortlist/list/clear', $params);
    }

    public static function makeListDefaultAction($listId, $options = array())
    {
        $params['listId'] = $listId;
        $params['return'] = craft()->request->getUrl();
        if(isset($options['return'])) $params['return'] = $options['return'];

        return UrlHelper::getActionUrl('shortlist/list/makeDefault', $params);
    }



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


