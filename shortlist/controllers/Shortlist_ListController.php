<?php
namespace Craft;

class Shortlist_ListController extends BaseController
{
    protected $allowAnonymous = true;

    public function init()
    {
        // This will grab our current user be they member or guest
        craft()->shortlist->getUser();
    }


    /**
     * Add, Remove, Toggle
     *
     * All are just stub functions passing to handleAction where all the core logic is shared
     */
    public function actionAdd()
    {
        $this->handleAction('new');
    }

    public function actionNew()
    {
        $this->handleAction('new');
    }

    public function actionRemove()
    {
        $this->handleAction('remove');
    }

    public function actionDelete()
    {
        $this->handleAction('remove');
    }

    public function actionMakeDefault()
    {
        $this->handleAction('makeDefault');
    }


    /**
     * Handle Action
     *
     * Does all the leg work for the common actions
     */
    public function handleAction($actionType)
    {

        // Pass to the service to do the leg work
        $response = craft()->shortlist_list->action($actionType);
        if ($response == false) {
            // Deal with an error state
            die('failed to add'); // @todo
        }


        // Return as appropriate
        if (craft()->request->isAjaxRequest()) {
            $this->returnJson($response);
        } else {
            craft()->shortlist->redirect($response['object']);
        }

    }


}