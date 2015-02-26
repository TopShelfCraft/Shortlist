<?php
namespace Craft;

class Shortlist_ListModel extends BaseElementModel
{

    protected $elementType = 'Shortlist_List';

    public function __construct($attributes = null)
    {
        $settings = craft()->plugins->getPlugin('shortlist')->getSettings();

        $this->name = $settings->defaultListName;
        $this->title = $settings->defaultListTitle;
        $this->slug = $settings->defaultListSlug;
        $this->ownerId = craft()->shortlist->user->id;
        $this->ownerType = craft()->shortlist->user->type;
        $this->default = false;

        parent::__construct($attributes);
    }

    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'id'        => AttributeType::Number,
            'name'      => array(AttributeType::String, 'required' => true),
            'default'   => array(AttributeType::Bool, 'default' => false, 'required' => true),
            'title'     => array(AttributeType::String, 'required' => true),
            'slug'      => array(AttributeType::String, 'required' => true),
            'userSlug'  => array(AttributeType::String, 'required' => true),
            'shareSlug' => array(AttributeType::String),
            'public'    => array(AttributeType::Bool, 'default' => true),
            'type'      => array(AttributeType::String, 'default' => 'user'),
            'ownerId'   => array(AttributeType::String, 'label' => 'Owner Id', 'required' => true),
            'ownerType' => array(AttributeType::Enum, 'values' => array(Shortlist_OwnerType::Member, Shortlist_OwnerType::Guest), 'default' => Shortlist_OwnerType::Guest, 'label' => 'Owner Type'),

        ));
    }


    /**
     * Returns whether the current user can edit the element.
     *
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }


    /**
     * Returns the element's CP edit URL.
     *
     * @return string|false
     */
    public function getCpEditUrl()
    {
        return UrlHelper::getCpUrl('shortlist/list/' . $this->id);
    }

    /**
     * Returns an list's items
     *
     * @return array()
     */
    public function items()
    {
        $items = craft()->shortlist_item->findByList($this->id);

        return $items;
    }

    /*
     * Returns a list's owner
     *
     * @return UserModel
     */
    public function owner()
    {

        if ($this->ownerType == 'member') {
            $user = craft()->users->getUserById($this->ownerId);
        } else {
            $user = new UserModel();
            $user->username = 'Guest';
        }

        return $user;
    }
}
