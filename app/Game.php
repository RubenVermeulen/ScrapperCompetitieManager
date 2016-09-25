<?php

namespace Project;


class Game extends Model
{
    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'match_id',
        'type',
    ];

    // Relations

    public function match() {
        return $this->belongsTo('Project\Match', 'match_id');
    }

    public function sets() {
        return $this->hasMany('Project\Set', 'game_id');
    }
}