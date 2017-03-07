<?php
namespace Craft;

class Shortlist_UserModel extends BaseModel
{

    public function __construct()
    {
        $user = craft()->userSession->getUser();
        if ($user) {
            // Do we have a super user condition?
            if(craft()->httpSession->get('Shortlist_SuperUser') != '') {
                $this->id = craft()->httpSession->get('Shortlist_SuperUser');
                $this->type = Shortlist_OwnerType::Member;
            }
            else {
                $this->id = $user->id;
                $this->type = Shortlist_OwnerType::Member;
            }
        } else {
            // Find if we have a guest session
            $this->id = craft()->httpSession->getSessionID();
            $this->type = Shortlist_OwnerType::Guest;
        }
    }

    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'id'   => AttributeType::Number,
            'type' => array(AttributeType::Number, 'required' => true)
        ));
    }


}
