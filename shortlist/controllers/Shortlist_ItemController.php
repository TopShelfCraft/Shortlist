<?php
namespace Craft;

class Shortlist_ItemController extends BaseController
{
	protected $allowAnonymous = true;


	/**
	* Add, Remove, Toggle
	*
	* All are just stub functions passing to handleAction where all the core logic is shared
	*/
	public function actionAdd(){ $this->handleAction('add'); }
	public function actionRemove(){ $this->handleAction('remove'); }
	public function actionToggle(){ $this->handleAction('toggle'); }


	/**
	* Handle Action
	*
	* Does all the leg work for the common actions
	*/
	public function handleAction($actionType)
	{
		// Collect the info
		// at the very least we need an item id
		$itemId = $this->getIdForRequest();
		if($itemId == false) {
			// Return an error message
			die('no id'); // @todo
		}

		// Validate this id is a real element id
		// @todo

		// Get the list for the request
		// We might have a list_id on the request.
		// dont worry too much if we haven't got it though -
		// we'll use the default list if it doesn't exist, or worst
		// case we'll create a new list on the action if we need to
		$listId = $this->getIdForRequest('listId', 1);

		// Get any extra values
		$extra = array(); // @todo

		// Pass to the service to do the leg work
		$response = craft()->shortlist_item->action($actionType, $itemId, $listId, $extra);
		if($response == false) {
			// Deal with an error state
			die('failed to add'); // @todo
		}

		// Success.
		// Return as appropriate
		die('done');

	}



	private function getIdForRequest($name = 'id', $offset = 0)
	{
		$id = false;

		if(craft()->request->getPost($name)) {
			$id = craft()->request->getPost($name);
		} elseif(craft()->request->getQuery($name)) {
			$id = craft()->request->getQuery($name);
		} else {
			// Try to extract it from the request segments
			$count = 0;
			foreach(craft()->request->getSegments() as $pos => $segment) {
				if(is_numeric($segment) && !is_numeric($id)) {
					$count++;
					if($count > $offset) {
						$id = $segment;
					}
				}
			}
		}


		if(is_numeric($id)) return $id;

		return false;
	}

}