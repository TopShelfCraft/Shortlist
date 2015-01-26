<?php
namespace Craft;

class ShortlistHelper
{

	public static function addAction($elementId)
	{
		$params = array();
		$params['id'] = $elementId;
		$params['return'] = craft()->request->getUrl();

		return UrlHelper::getActionUrl('shortlist/item/add', $params);
	}

	public static function removeAction($itemId)
	{
		$params = array();
		$params['itemId'] = $itemId;
		$params['return'] = craft()->request->getUrl();

		return UrlHelper::getActionUrl('shortlist/item/remove', $params);
	}

	public static function associateResults($resultset, $key, $sort = FALSE)
	{
		$array = array();

		foreach ($resultset AS $row)
		{
			$array[$row->$key] = $row;
		}

		if ($sort === TRUE)
		{
			ksort($array);
		}

		return $array;
	}
}


