<?php
namespace Craft;

class Shortlist_UserModel extends BaseModel
{

    public function __construct()
    {
    	$user = craft()->userSession->getUser();
        if ($user) {
        	$this->id = $user->id;
        	$this->type = Shortlist_OwnerType::Member;
        } else {
        	// Find if we have a guest session
        	// @todo - make real guest retention
        	$this->id = StringHelper::UUID();
        	$this->type = Shortlist_OwnerType::Guest;
        }
    }

    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'id'                => AttributeType::Number,
            'type'         => array(AttributeType::Number, 'required' => true)
        ));
    }



}
