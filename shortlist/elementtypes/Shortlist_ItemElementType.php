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
}
