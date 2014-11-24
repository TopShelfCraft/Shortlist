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
		$model = Shortlist_ListModel::populateModel($row);

		return $model;
	}
}
