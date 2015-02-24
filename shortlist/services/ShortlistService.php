<?php

namespace Craft;

class ShortlistService extends BaseApplicationComponent
{
    public $user = null;
    private $_cache;
    private $_cacheElementIds;


    public function __construct()
    {
        $this->getUser();
    }

    public function getUser()
    {
        $this->user = new Shortlist_UserModel();
    }


    public function redirect($object = null)
    {
        $url = craft()->request->getPost('redirect');

        if ($url === null) {
            $url = craft()->request->getParam('return');

            if ($url === null) {
                $url = craft()->request->getUrlReferrer();

                if ($url === null) {
                    $url = '/';
                }
            }
        }

        if ($object) {
            $url = craft()->templates->renderObjectTemplate($url, $object);
        }

        craft()->request->redirect($url);
    }


    public function getIdForRequest($name = 'id', $offset = 0)
    {
        $id = false;
        // Explode our name if required
        $names = explode(',', $name);

        foreach ($names as $n) {
            if ($id === false || $id == '' || is_null($id)) {
                if (craft()->request->getPost($n)) {
                    $id = craft()->request->getPost($n);
                } elseif (craft()->request->getQuery($n)) {
                    $id = craft()->request->getQuery($n);
                }
            }
        }

        if ($id === false || $id == '' || is_null($id)) {
            // Try to extract it from the request segments
            $count = 0;
            foreach (craft()->request->getSegments() as $pos => $segment) {
                if (is_numeric($segment) && !is_numeric($id)) {
                    $count++;
                    if ($count > $offset) {
                        $id = $segment;
                    }
                }
            }
        }

        if (is_numeric($id)) return $id;

        return false;
    }


    public function getExtraForRequest($baseFields = array())
    {
        $data = array();
        // field[] data
        // @todo

        foreach ($baseFields as $n) {
            $val = craft()->request->getParam($n);

            if(!is_null($val)) {
                $data[$n] = $val;
            }
        }

        return $data;
    }



}