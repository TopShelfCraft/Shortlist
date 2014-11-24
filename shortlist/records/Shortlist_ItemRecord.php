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

            'listId'            => array(AttributeType::String, 'label' => 'List Id', 'required' => true),
            'public'        	=> array(AttributeType::Bool, 'label' => 'Public Item', 'default' => true, 'required' => true),
            'type'             	=> array(AttributeType::String, 'label' => 'Item Type','default' => 'manual'),
            'order'             => array(AttributeType::Number, 'label' => 'Item Order', 'default' => 0),
            'addedOn'           => array(AttributeType::DateTime, 'label' => 'Added On'),
            'updatedOn'         => array(AttributeType::DateTime, 'label' => 'Updated On'),
            'addedByUserId'     => array(AttributeType::Number, 'label' => 'Added By', 'required' => true)
        );
    }


}



