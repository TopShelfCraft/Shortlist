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

}
