<?php

use Phinx\Migration\AbstractMigration;

class CreateMatchesTable extends AbstractMigration
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
        $table = $this->table('matches');

        $table
            ->addColumn('tracking_id', 'integer')

            ->addColumn('draw_id', 'integer')
            ->addForeignKey('draw_id', 'draws', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])

            ->addColumn('home_team_id', 'integer')
            ->addForeignKey('home_team_id', 'teams', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])

            ->addColumn('away_team_id', 'integer')
            ->addForeignKey('away_team_id', 'teams', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])

            ->addIndex(['tracking_id', 'draw_id'], ['unique' => true])

            ->addColumn('result', 'string', ['limit' => 3, 'null' => true])
            ->addColumn('forfeit', 'boolean')

            ->addTimestamps()

            ->addColumn('played_at', 'timestamp')

            ->save();
    }
}
