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
            'elementId'   => array(AttributeType::Number, 'label' => 'Element ID', 'required' => true),
            'elementType' => array(AttributeType::String, 'label' => 'Element Type', 'required' => true),
            'listId'      => array(AttributeType::String, 'label' => 'List Id', 'required' => true),
            'public'      => array(AttributeType::Bool, 'label' => 'Public Item', 'default' => true, 'required' => true),
            'type'        => array(AttributeType::String, 'label' => 'Item Type', 'default' => 'manual'),
            'sortOrder'   => array(AttributeType::Number, 'label' => 'Item Order', 'default' => 0),
            'deleted'     => array(AttributeType::Bool, 'label' => 'Item Deleted', 'default' => false)
        );
    }

    public function findByAttributes($attributes, $condition = '', $params = array())
    {
        if (!isset($attributes['deleted'])) {
            $attributes['deleted'] = false;
        }

        return parent::findByAttributes($attributes, $condition, $params);
    }


    public function findAllByAttributes($attributes, $condition = '', $params = array())
    {
        if (!isset($attributes['deleted'])) {
            $attributes['deleted'] = false;
        }

        return parent::findAllByAttributes($attributes, $condition, $params);
    }

}



