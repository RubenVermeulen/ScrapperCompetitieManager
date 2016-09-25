<?php

use Phinx\Migration\AbstractMigration;

class CreatePlayersTable extends AbstractMigration
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
        $table = $this->table('players');

        $table
            ->addColumn('membership_id', 'integer')
            ->addIndex(['membership_id'], ['unique' => true])

            ->addColumn('first_name', 'string', ['limit' => 255])
            ->addColumn('last_name', 'string', ['limit' => 255])
            ->addColumn('gender', 'boolean')
            ->addColumn('ranking_single', 'string', ['limit' => 2])
            ->addColumn('ranking_double', 'string', ['limit' => 2])
            ->addColumn('ranking_mix', 'string', ['limit' => 2])

            ->addTimestamps()

            ->save();
    }
}
