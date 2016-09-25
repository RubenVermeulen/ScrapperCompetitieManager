<?php

namespace Project;


class GamesPlayers extends Model
{
    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'game_id',
        'player_id',
    ];
}