<?php

namespace Craft;

class ShortlistService extends BaseApplicationComponent
{
	public $user = null;
	private $_cache;
	private $_cacheElementIds;


	public function getUser()
	{
		$this->user = new Shortlist_UserModel();
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