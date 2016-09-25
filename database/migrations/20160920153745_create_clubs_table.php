<?php

use Phinx\Migration\AbstractMigration;

class CreateClubsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('clubs');

        $table
            ->addColumn('tracking_id', 'string', ['limit' => 36])
            ->addIndex(['tracking_id'], ['unique' => true])

            ->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('address', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('contact_person', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('tel', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('email', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('website', 'string', ['limit' => 255, 'null' => true])
            ->addTimestamps()
            ->save();
    }
}
