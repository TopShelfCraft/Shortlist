<?php
namespace Craft;

class Shortlist_ListRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'shortlist_list';
    }

    protected function defineAttributes()
    {
        return array(
            'name'      => array(AttributeType::String, 'label' => 'List Name', 'required' => true),
            'title'     => array(AttributeType::String, 'label' => 'List Title', 'required' => true),
            'default'   => array(AttributeType::Bool, 'label' => 'Default List', 'default' => true),
            'slug'      => array(AttributeType::String, 'label' => 'List Slug (ie. {{username-listName}})', 'required' => true),
            'userSlug'  => array(AttributeType::String, 'label' => 'List User Slug (ie. {{listName}})', 'required' => true),
            'shareSlug' => array(AttributeType::String, 'label' => 'List Share Slug (ie. {{rand(10)}})', 'required' => true),
            'public'    => array(AttributeType::Bool, 'label' => 'Public List', 'default' => true, 'required' => true),
            'type'      => array(AttributeType::String, 'label' => 'List Type', 'default' => 'manual'),
            'ownerId'   => array(AttributeType::String, 'label' => 'Owner Id', 'required' => true),
            'ownerType' => array(AttributeType::Enum, 'values' => array(Shortlist_OwnerType::Member, Shortlist_OwnerType::Guest), 'default' => Shortlist_OwnerType::Guest, 'label' => 'Owner Type'),

        );
    }


}



