<?php

namespace Craft;

class Shortlist_ItemService extends BaseApplicationComponent
{

    public function action($actionType, $elementId, $listId = false, $extraData = array())
    {
        // Get the list in question
        $list = craft()->shortlist_list->getListOrCreate($listId);

        die('<pre>'.print_R($list,1));
        die($actionType);
        die('hi');
    }


}
