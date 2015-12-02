<?php
namespace Craft;

class Shortlist_UpgradeInfoModel extends BaseModel
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
            'editions'        => array(AttributeType::Mixed, 'required' => true),
            'stripePublicKey' => array(AttributeType::String, 'required' => true),
        );
    }
}
