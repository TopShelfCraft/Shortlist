<?php
namespace Craft;

class ShortlistController extends BaseController
{
    protected $allowAnonymous = true;
    private $plugin;

    public function init()
    {
        $this->plugin = craft()->plugins->getPlugin('shortlist');

        if (!$this->plugin) {
            throw new Exception('Couldn’t find the Shortlist plugin!');
        }
    }

    public function returnError()
    {
        // Do we have errors?
        if(!empty(craft()->shortlist->errors)) {
            // We have slightly different formats depending on if it's a single or multiple set of errors
            $msg = '';

            if(count(craft()->shortlist->errors) > 1) {
                $msg = '<ul>';
                foreach(craft()->shortlist->errors as $error) {
                    $msg .= '<li>'.$error.'</li>';
                }
                $msg .= '</ul>';
            } else {
                $msg = current(craft()->shortlist->errors);
            }
        }

        craft()->userSession->setError('There was a problem with that action - '.$msg);


        $url = craft()->request->getUrlReferrer();
        craft()->request->redirect($url);
    }

}
