<?php
namespace Craft;

/**
 * Shortlist List element type
 */
class Shortlist_ListElementType extends BaseElementType
{

    private $listInlineViewLimit = 10;
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
            ->addSelect('shortlist_list.name, shortlist_list.title, shortlist_list.default, shortlist_list.slug, shortlist_list.userSlug, shortlist_list.shareSlug, shortlist_list.public, shortlist_list.type, shortlist_list.ownerId, shortlist_list.ownerType, shortlist_list.deleted')
            ->join('shortlist_list shortlist_list', 'shortlist_list.id = elements.id');


        if ($criteria->name) {
            $query->andWhere(DbHelper::parseParam('shortlist_list.name', $criteria->name, $query->params));
        }

        if ($criteria->isDeleted) {
            $query->andWhere(DbHelper::parseParam('shortlist_list.deleted', $criteria->isDeleted, $query->params));
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
            'userSlug'  => AttributeType::String,
            'shareSlug' => AttributeType::String,
            'key'       => AttributeType::String,
            'isDeleted' => AttributeType::Bool
        );
    }

    /**
     * Returns the attributes that can be shown/sorted by in table views.
     *
     * @param string|null $source
     * @return array
     */
    public function defineTableAttributes($source = null)
    {
        return array(
            'title'       => Craft::t('Title'),
            'slug'        => Craft::t('Slug'),
            'owner'       => Craft::t('Owner'),
            'dateCreated' => Craft::t('Created On'),
            'dateUpdated' => Craft::t('Updated On'),
            'itemCount'   => Craft::t('Item Count'),
            'itemList'    => Craft::t('Items')
        );
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
            case 'owner': {
                if ($element->ownerType == 'guest') {
                    return "<a href='shortlist/user/".$element->ownerId."'>".Craft::t('Guest')."</a>";
                } else {
                    $user = craft()->users->getUserById($element->ownerId);


                    if($user == null) {
                        return Craft::t('[Deleted User]');
                    } else {
                        return "<a href='shortlist/user/".$user->id."'>".$user->getFriendlyName()."</a>";
                    }
                }
            }
            case 'itemCount' : {
                return count($element->items());
            }
            case 'itemList' : {
                $items = $element->items();


                $str = array();
                $i = 0;
                foreach($items as $item) {
                    if($i < $this->listInlineViewLimit) {
                        $parent = craft()->entries->getEntryById($item->elementId);
                        $url = 'shortlist/item/' . $item->elementId;
                        $str[] = '<a href="' . $url . '">' . $parent->title . '</a>';
                    }
                    $i++;
                }
                $ret = implode(', ', $str);

                if(count($items) > $this->listInlineViewLimit) {
                    $hidden = count($items) - $this->listInlineViewLimit;
                    $moreUrl = 'shortlist/list/'.$element->id.'#items';
                    $ret .= " .. <a href='".$moreUrl."'>+".$hidden." more</a>";
                }
                return $ret;
            }
            case 'title' : {
                return $element->name;
            }
            default : {
                return $element->$attribute;
            }

        }
    }
}
