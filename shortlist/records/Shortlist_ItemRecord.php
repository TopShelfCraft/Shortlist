<?php
namespace Craft;

class Shortlist_ItemRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'shortlist_item';
    }

    protected function defineAttributes()
    {
        return array(
            'elementId'         => array(AttributeType::Number, 'label' => 'Element ID', 'required' => true),
            'listId'            => array(AttributeType::String, 'label' => 'List Id', 'required' => true),
            'public'        	=> array(AttributeType::Bool, 'label' => 'Public Item', 'default' => true, 'required' => true),
            'type'             	=> array(AttributeType::String, 'label' => 'Item Type','default' => 'manual'),
            'order'             => array(AttributeType::Number, 'label' => 'Item Order', 'default' => 0)
        );
    }


}



