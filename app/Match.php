<?php

namespace Project;


class Match extends Model
{
    /**
     * Fillable fields.
     *
     * @var array
     */
    protected $fillable = [
        'tracking_id',
        'draw_id',
        'home_team_id',
        'away_team_id',
        'played_at',
        'result',
        'forfeit',
    ];

    protected $dates = ['played_at'];

    // Relations

    public function draw() {
        return $this->belongsTo('Project\Draw', 'draw_id');
    }

    public function homeTeam() {
        return $this->belongsTo('Project\Team', 'home_team_id');
    }

    public function awayTeam() {
        return $this->belongsTo('Project\Team', 'away_team_id');
    }

    public function games() {
        return $this->hasMany('Project\Game', 'match_id');
    }
}