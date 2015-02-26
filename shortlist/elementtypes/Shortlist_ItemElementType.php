<?php
namespace Craft;

/**
 * Shortlist Item element type
 */
class Shortlist_ItemElementType extends BaseElementType
{
    /**
     * Returns the element type name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Shortlist Item');
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
            '*' => array('label' => Craft::t('All Items')),
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
        $model = Shortlist_ItemModel::populateModel($row);

        return $model;
    }

    /**
     * Defines any custom element criteria attributes for this element type.
     *
     * @return array
     */
    public function defineCriteriaAttributes()
    {
        return array(
            'elementId'   => AttributeType::Mixed,
            'elementType' => AttributeType::String,
            'title'       => AttributeType::String,
            'listId'      => AttributeType::Mixed
        );
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
            ->addSelect('shortlist_item.elementId, shortlist_item.elementType, shortlist_item.listId, shortlist_list.ownerId')
            ->join('shortlist_item shortlist_item', 'shortlist_item.id = elements.id')
            ->join('shortlist_list shortlist_list', 'shortlist_item.listId = shortlist_list.id');

        /*
                if ($criteria->ownerId) {
                    $query->andWhere(DbHelper::parseParam('shortlist_list.ownerId', $criteria->ownerId, $query->params));
                }*/

        if ($criteria->listId) {
            $query->andWhere(DbHelper::parseParam('shortlist_item.listId', $criteria->listId, $query->params));
        }


        if ($criteria->elementId) {
            $query->andWhere(DbHelper::parseParam('shortlist_item.elementId', $criteria->elementId, $query->params));
        }

        /*

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
                }*/
    }


    /**
     * Returns the table view HTML for a given attribute.
     *
     * @param BaseElementModel $element
     * @param string $attribute
     * @return string
     */
    public function getTableAttributeHtml(BaseElementModel $element, $attribute)
    {

        switch ($attribute) {
            case 'title' : {
                $type = $element->elementType;
                $parent = craft()->entries->getEntryById($element->elementId);

                return $parent->title;
            }
            default : {
                return $element->$attribute;
            }

        }
    }
}
