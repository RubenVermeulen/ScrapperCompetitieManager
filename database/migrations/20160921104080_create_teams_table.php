<?php

use Phinx\Migration\AbstractMigration;

class CreateTeamsTable extends AbstractMigration
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
        $table = $this->table('teams');

        $table
            ->addColumn('tracking_id', 'integer', ['null' => true])

            ->addColumn('club_id', 'integer', ['null' => true])
            ->addForeignKey('club_id', 'clubs', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])

            ->addColumn('competition_id', 'integer')
            ->addForeignKey('competition_id', 'competitions', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])

            ->addColumn('draw_id', 'integer')
            ->addForeignKey('draw_id', 'draws', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])

            ->addIndex(['club_id', 'competition_id', 'draw_id'], ['unique' => true])

            ->addColumn('name', 'string', ['limit' => 255])
            ->addTimestamps()
            ->save();
    }
}
