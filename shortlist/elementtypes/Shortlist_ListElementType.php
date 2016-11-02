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
     * Returns whether this element type has statuses.
     *
     * @return bool
     */
    public function hasStatuses()
    {
        return true;
    }



    /**
     * Returns whether this element type has content.
     *
     * @return bool
     */
    public function hasTitles()
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
        $sources = array(
            '*'       => array('label' => Craft::t('All Lists'))
        );

        //die('<prE>'.print_R($sources,1));
        return $sources;

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
            ->addSelect('shortlist_list.default, shortlist_list.userSlug, shortlist_list.hash, shortlist_list.public, shortlist_list.type, shortlist_list.ownerId, shortlist_list.ownerType')
            ->join('shortlist_list shortlist_list', 'shortlist_list.id = elements.id');

        if ($criteria->default) {
            $query->andWhere(DbHelper::parseParam('shortlist_list.default', $criteria->default, $query->params));
        }
        if ($criteria->userSlug) {
            $query->andWhere(DbHelper::parseParam('shortlist_list.userSlug', $criteria->userSlug, $query->params));
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
        if ($criteria->hash) {
            $query->andWhere(DbHelper::parseParam('shortlist_list.hash', $criteria->hash, $query->params));
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
            'ownerId'   => AttributeType::String,
            'ownerType' => AttributeType::String,
            'public'    => AttributeType::Bool,
            'default'   => AttributeType::Bool,
            'userSlug'  => AttributeType::String,
            'hash' => AttributeType::String,
            'key'       => AttributeType::String,
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
            'id'          => Craft::t('Id'),
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
                    return Craft::t('Guest');
                } else {
                    $user = craft()->users->getUserById($element->ownerId);

                    if ($user == null) {
                        return Craft::t('[Deleted User]');
                    } else {
                        $url = UrlHelper::getCpUrl('users/'.$user->id);
                        return "<a href='".$url."'>" . $user->getFriendlyName() . "</a>";
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
                foreach ($items as $item) {
                    if ($i < $this->listInlineViewLimit) {
                        $parent = craft()->elements->getElementById($item->elementId);
                        $url = UrlHelper::getCpUrl('shortlist/list/'.$element->id.'#' . $item->elementId);
                        $str[] = '<a href="' . $url . '">' . $parent->title . '</a>';
                    }
                    $i++;
                }
                $ret = implode(', ', $str);

                if (count($items) > $this->listInlineViewLimit) {
                    $hidden = count($items) - $this->listInlineViewLimit;
                    $moreUrl = UrlHelper::getCpUrl('shortlist/list/' . $element->id . '#items');
                    $ret .= " .. <a href='" . $moreUrl . "'>+" . $hidden . " more</a>";
                }

                return $ret;
            }
            default : {
                return $element->$attribute;
            }

        }
    }
}
