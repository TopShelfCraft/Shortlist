<?php
namespace Craft;

class m160126_000000_shortlist_updateListShareSlug extends BaseMigration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        $chargesTable = $this->dbConnection->schema->getTable('{{shortlist_list}}');

        if ($chargesTable->getColumn('shareSlug') === null)
        {
            // Add the 'hash' column to the charges table
            $this->addColumnAfter('shortlist_list', 'shareSlug', array('column' => ColumnType::Varchar, 'required' => true), 'default');
            $this->dropColumn('shortlist_list', 'userSlug');
        }

        return true;
    }
}
