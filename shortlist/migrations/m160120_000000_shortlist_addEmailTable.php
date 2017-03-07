<?php
namespace Craft;

class m160120_000000_shortlist_addEmailTable extends BaseMigration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
         // Create the craft_charge_customers table
        craft()->db->createCommand()->createTable('shortlist_emails', [
                'name'         => ['required' => true],
                'handle'       => ['required' => true],
                'subject'      => ['required' => true],
                'to'           => ['required' => true],
                'bcc'          => [],
                'enabled'      => ['column' => ColumnType::Bool, 'required' => true],
                'templatePath' => ['required' => true]]
            , null, true);

        // Add indexes to shortlist_emails
        craft()->db->createCommand()->createIndex('shortlist_emails', 'handle', true);

        return true;
    }
}
