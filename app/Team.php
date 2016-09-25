<?php

namespace Project;

class Team extends Model
{
    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'tracking_id',
        'club_id',
        'competition_id',
        'draw_id',
        'name',
    ];

    // Relations

    public function competition() {
        return $this->belongsTo('Project\Competition', 'competition_id');
    }

    public function players() {
        return $this->hasMany('Project\Player', 'player_id');
    }
}