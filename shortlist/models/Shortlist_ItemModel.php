<?php
namespace Craft;

class Shortlist_ItemModel extends BaseElementModel
{
    protected $elementType = 'Shortlist_Item';

    public function __construct($attributes = null)
    {
        $settings = craft()->plugins->getPlugin('shortlist')->getSettings();

        $this->listName = $settings->defaultListName;


        parent::__construct($attributes);
    }

    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'id'          => array(AttributeType::Number),
            'elementId'   => array(AttributeType::Number, 'required' => true),
            'elementType' => array(AttributeType::String, 'required' => true),
            'listId'      => array(AttributeType::Number),
            'listName'    => array('AttrributeType::String', 'required' => true),
            'public'      => array(AttributeType::Bool, 'default' => true),
            'type'        => array(AttributeType::String, 'label' => 'Item Type'),
            'sortOrder'   => array(AttributeType::Number),
            'inList'      => array(AttributeType::Bool, 'required' => true, 'default' => false),
            'otherLists'  => array(AttributeType::Mixed),
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
        return UrlHelper::getCpUrl('shortlist/item/' . $this->id);
    }

    /*
     * Element
     *
     * Gets the parent element for an item
     */
    public function element()
    {
        return craft()->shortlist_item->findParentElement($this->elementId);
    }

    /*
     * Title
     *
     * Shortcut to get the title of the parent element
     *
     */
    public function title()
    {
        $parent = craft()->shortlist_item->findParentElement($this->elementId);
        return $parent->title;
    }

    /**
     * Returns an item's lists
     *
     * @return array()
     */
    public function lists()
    {
        $lists = craft()->shortlist_list->getLists();
        return $lists;
    }

    public function add($options = array())
    {
        return ShortlistHelper::addAction($this->elementId, $this->listId, $options);
    }

    public function remove($options = array())
    {
        return ShortlistHelper::removeAction($this->elementId, $this->listId, $options);
    }

    public function toggle($options = array())
    {
        return ShortlistHelper::toggleAction($this->elementId, $this->listId, $options);
    }

    public function parentList()
    {
        return craft()->shortlist_list->getListByIdOrBare($this->listId);
    }


}
