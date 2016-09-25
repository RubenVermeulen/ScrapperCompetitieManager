<?php

use Phinx\Migration\AbstractMigration;

class CreateGamesPlayersTable extends AbstractMigration
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
        $table = $this->table('games_players');

        $table
            ->addColumn('game_id', 'integer')
            ->addForeignKey('game_id', 'games', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])

            ->addColumn('player_id', 'integer')
            ->addForeignKey('player_id', 'players', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])

            ->addIndex(['game_id', 'player_id'], ['unique'=> true])

            ->addTimestamps()

            ->save();
    }
}
