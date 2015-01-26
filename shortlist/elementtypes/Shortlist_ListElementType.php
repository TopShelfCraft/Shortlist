<?php
namespace Craft;

/**
 * Shortlist List element type
 */
class Shortlist_ListElementType extends BaseElementType
{
    /**
     * Returns the element type name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Shortlist List');
    }


    /**
     * Returns whether this element type has content.
     *
     * @return bool
     */
    public function hasContent()
    {
        return true;
    }

    /**
     * Returns this element type's sources.
     *
     * @param string|null $context
     * @return array|false
     */
    public function getSources($context = null)
    {
        return array(
            '*' => array('label' => Craft::t('All Lists')),
        );
    }


    /**
     * Populates an element model based on a query result.
     *
     * @param array $row
     * @return array
     */
    public function populateElementModel($row)
    {
        return Shortlist_ListModel::populateModel($row);
    }

    /**
     * Modifies an element query targeting elements of this type.
     *
     * @param DbCommand $query
     * @param ElementCriteriaModel $criteria
     * @return mixed
     */
    public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
    {
        $query
            ->addSelect('shortlist_list.name, shortlist_list.title, shortlist_list.default, shortlist_list.slug, shortlist_list.userSlug, shortlist_list.shareSlug, shortlist_list.public, shortlist_list.type, shortlist_list.ownerId, shortlist_list.ownerType')
            ->join('shortlist_list shortlist_list', 'shortlist_list.id = elements.id');


        if ($criteria->name) {
            $query->andWhere(DbHelper::parseParam('shortlist_list.name', $criteria->name, $query->params));
        }

        if ($criteria->title) {
            $query->andWhere(DbHelper::parseParam('shortlist_list.title', $criteria->title, $query->params));
        }

        if ($criteria->default) {
            $query->andWhere(DbHelper::parseParam('shortlist_list.default', $criteria->default, $query->params));
        }
        if ($criteria->slug) {
            $query->andWhere(DbHelper::parseParam('shortlist_list.slug', $criteria->slug, $query->params));
        }
        if ($criteria->userSlug) {
            $query->andWhere(DbHelper::parseParam('shortlist_list.userSlug', $criteria->userSlug, $query->params));
        }
        if ($criteria->shareSlug) {
            $query->andWhere(DbHelper::parseParam('shortlist_list.shareSlug', $criteria->shareSlug, $query->params));
        }
        if ($criteria->public) {
            $query->andWhere(DbHelper::parseParam('shortlist_list.public', $criteria->public, $query->params));
        }
        if ($criteria->ownerId) {
            $query->andWhere(DbHelper::parseParam('shortlist_list.ownerId', $criteria->ownerId, $query->params));
        }
        if ($criteria->ownerType) {
            $query->andWhere(DbHelper::parseParam('shortlist_list.ownerType', $criteria->ownerType, $query->params));
        }
    }

    /**
     * Defines any custom element criteria attributes for this element type.
     *
     * @return array
     */
    public function defineCriteriaAttributes()
    {
        return array(
            'name'      => AttributeType::Mixed,
            'title'     => AttributeType::Mixed,
            'slug'      => AttributeType::Mixed,
            'ownerId'   => AttributeType::String,
            'ownerType' => AttributeType::String,
            'public'    => AttributeType::Bool,
            'default'   => AttributeType::Bool,
            'slug'      => AttributeType::String,
            'userSlug'  => AttributeType::String,
            'shareSlug' => AttributeType::String,
            'key'       => AttributeType::String
        );
    }

}
