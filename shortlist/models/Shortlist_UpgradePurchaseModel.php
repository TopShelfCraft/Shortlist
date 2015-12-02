<?php
namespace Craft;


class Shortlist_UpgradePurchaseModel extends BaseModel
{
    // Protected Methods
    // =========================================================================

    /**
     * @inheritDoc BaseModel::defineAttributes()
     *
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
            'ccTokenId'     => [AttributeType::String, 'required' => true],
            'product'       => [AttributeType::String, 'required' => true],
            'edition'       => [AttributeType::Number, 'required' => true],
            'expectedPrice' => [AttributeType::Number, 'required' => true],
            'success'       => [AttributeType::Bool],
        );
    }
}
