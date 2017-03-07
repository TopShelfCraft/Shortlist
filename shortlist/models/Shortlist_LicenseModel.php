<?php
namespace Craft;

class Shortlist_LicenseModel extends BaseModel
{
    // Public Methods
    // =========================================================================

    /**
     * @return null
     */
    public function decode()
    {
        echo JsonHelper::decode($this);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritDoc BaseModel::defineAttributes()
     *
     * @return array
     */
    protected function defineAttributes()
    {
        return [
            'requestUrl'  => [AttributeType::String],
            'requestIp'   => [AttributeType::String],
            'requestTime' => [AttributeType::String],
            'requestPort' => [AttributeType::String],

            'craftBuild'   => [AttributeType::String],
            'craftVersion' => [AttributeType::String],
            'craftEdition' => [AttributeType::String],
            'craftTrack'   => [AttributeType::String],
            'userEmail'    => [AttributeType::String],

            'licenseKey'      => [AttributeType::String],
            'licensedEdition' => [AttributeType::String],
            'requestProduct'  => [AttributeType::String],
            'requestVersion'  => [AttributeType::String],
            'data'            => [AttributeType::Mixed],
            'errors'          => [AttributeType::Mixed]
        ];

    }
}
