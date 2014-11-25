<?php

namespace Craft;

class ShortlistService extends BaseApplicationComponent
{
	public $user = null;
	private static $_cache;
	private static $_cacheElementIds;


	public function getUser()
	{
		$this->user = new Shortlist_UserModel();
	}

	public function getItemInfo($elementId = null)
	{
		if($this->user == null) $this->user = new Shortlist_UserModel();

		if($_cache == null) {
			// No cache - populate it
			$this->populateInfoCache();
		}

		die('find in cache - if not there, not in lists. ');
	}



	public function redirect($object = null)
	{
		$url = craft()->request->getPost('redirect');

		if ($url === null)
		{
			$url = craft()->request->getParam('return');

			if($url === null)
			{
				$url = craft()->request->getUrlReferrer();

				if($url === null)
				{
					$url = '/';
				}
			}
		}

		if ($object)
		{
			$url = craft()->templates->renderObjectTemplate($url, $object);
		}

		craft()->request->redirect($url);
	}

}