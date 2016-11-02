<?php
namespace Craft;

class m161102_000000_shortlist_addListHash extends BaseMigration
{
    public function safeUp()
    {
        // Add the hash to the shortlist list table
        $this->addColumn('shortlist_list', 'hash', ColumnType::Varchar);
        $this->dropColumn('shortlist_list', 'shareSlug');

        // also now generate unique hashes for all the new hash columns
        $this->populateHashes();
    }

    private function populateHashes()
    {
        $tableName = craft()->db->addTablePrefix('shortlist_list');
        $sql = 'UPDATE '.$tableName.' SET hash = MD5(RAND())';

        craft()->db->createCommand($sql)->execute();
    }
}
