<?php
namespace Craft;

class Shortlist_ItemModel extends BaseElementModel
{

    protected $elementType = 'Shortlist_Item';

    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'id'                => AttributeType::Number,
            'listId'			=> array(AttributeType::Number, 'required' => true),
            'public'			=> array(AttributeType::Bool, 'default' => true),
            'type'				=> array(AttributeType::String, 'label' => 'Item Type'),
            'order'				=> array(AttributeType::Number),
            'addedOn'           => array(AttributeType::DateTime, 'label' => 'Added On', 'required' => true),
            'updatedOn'         => array(AttributeType::DateTime, 'label' => 'Updated On'),
            'addedByUserId'     => array(AttributeType::Number, 'required' => true)
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
        return UrlHelper::getCpUrl('shortlist/item/'.$this->id);
    }
}
