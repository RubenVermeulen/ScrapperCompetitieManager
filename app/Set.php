<?php

namespace Project;


class Set extends Model
{
    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'game_id',
        'result',
    ];

    // Relations

    public function match() {
        return $this->belongsTo('Project\Game', 'game_id');
    }
}